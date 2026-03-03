<?php

namespace App\Support\ExternalReviews;

use App\Models\VendorDetail;
use App\Models\VendorReview;
use Illuminate\Console\Command as ArtisanCommand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Throwable;

class VendorReviewFetcher
{
    private const REVIEW_HOST_KEYWORDS = [
        'trustpilot',
        'yell',
        'yelp',
        'facebook',
        'google',
    ];

    private const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36';

    private ?string $mapsKey;
    private string $mapsRegion;
    private ?string $geminiKey;
    private string $geminiModel;
    private bool $withDdg = false;
    private bool $skipPlaces = false;
    private bool $dryRun = false;

    public function __construct()
    {
        $this->mapsKey = config('services.google_maps.key', env('GOOGLE_MAPS_API_KEY')) ?: null;
        $this->mapsRegion = config('services.google_maps.region', env('GOOGLE_REGION', 'uk')) ?: 'uk';
        $this->geminiKey = config('services.gemini.key', env('GEMINI_API_KEY')) ?: null;
        $this->geminiModel = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-2.0-flash')) ?: 'gemini-2.0-flash';
    }

    public function run(ArtisanCommand $console, array $options = []): int
    {
        $this->withDdg = (bool) ($options['with_ddg'] ?? false);
        $this->skipPlaces = (bool) ($options['skip_places'] ?? false);
        $this->dryRun = (bool) ($options['dry_run'] ?? false);
        $limit = (int) ($options['limit'] ?? 0);
        $vendorOption = $options['vendor_id'] ?? null;
        $vendorIds = $this->parseVendorIds($vendorOption);

        if (!$this->skipPlaces && empty($this->mapsKey)) {
            $console->error('GOOGLE_MAPS_API_KEY is missing and Google Places lookups are enabled.');
            return SymfonyCommand::FAILURE;
        }

        if (!$this->skipPlaces && empty($this->geminiKey)) {
            $console->error('GEMINI_API_KEY is missing. Set it in .env before running this command.');
            return SymfonyCommand::FAILURE;
        }

        $console->info('Fetching vendor candidates...');

        $baseQuery = VendorDetail::query()
            ->with([
                'user:id,first_name,last_name,name,location,location_name,city,postcode,country',
                'products:id,vendor_id,title',
            ])
            ->whereNotNull('vendor_name')
            ->orderBy('id');

        if (!empty($vendorIds)) {
            $baseQuery->whereIn('id', $vendorIds);
        }

        $processed = 0;
        $stored = 0;
        $googleStored = 0;
        $ddgStored = 0;
        $skipped = 0;

        $baseQuery->chunkById(15, function (Collection $vendors) use (
            $console,
            $limit,
            &$processed,
            &$stored,
            &$googleStored,
            &$ddgStored,
            &$skipped
        ) {
            foreach ($vendors as $vendor) {
                if ($limit && $processed >= $limit) {
                    return false;
                }

                $processed++;
                $profile = $this->buildProviderProfile($vendor);
                if (!$profile) {
                    $skipped++;
                    $console->warn(sprintf('Skipping vendor #%d (missing profile data)', $vendor->id));
                    continue;
                }

                $console->info(sprintf('→ [%d] %s (%s)', $vendor->id, $profile['vendor_name'], $profile['location'] ?: 'unknown location'));

                try {
                    $results = $this->processVendor($console, $vendor, $profile);
                    $stored += $results['total'];
                    $googleStored += $results['google'];
                    $ddgStored += $results['ddg'];
                } catch (Throwable $e) {
                    $skipped++;
                    $console->error(sprintf('Failed vendor #%d: %s', $vendor->id, $e->getMessage()));
                    report($e);
                }
            }
        });

        $console->newLine();
        $console->info(sprintf('Processed %d vendors (%d skipped). Stored %d reviews (%d Google, %d open-web).', $processed, $skipped, $stored, $googleStored, $ddgStored));

        return SymfonyCommand::SUCCESS;
    }

    private function parseVendorIds($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_numeric($value)) {
            return [(int) $value];
        }

        if (is_array($value)) {
            return collect($value)->map(fn ($id) => (int) $id)->filter()->values()->all();
        }

        return collect(explode(',', (string) $value))
            ->map(fn ($chunk) => (int) trim($chunk))
            ->filter()
            ->values()
            ->all();
    }

    private function processVendor(ArtisanCommand $console, VendorDetail $vendor, array $profile): array
    {
        $queries = $this->buildProviderQueries($profile);
        $googleCount = 0;
        $ddgCount = 0;

        if (!$this->skipPlaces) {
            $match = $this->findBestPlace($console, $profile, $queries);
            if ($match && $match['place_id']) {
                $console->line(sprintf('  • Google Places candidate %s (confidence %.2f)', $match['place_id'], $match['confidence']));
                $googleCount = $this->ingestGoogleReviews($vendor, $profile, $match);
            } else {
                $console->line('  • No Google Places match');
            }
        }

        if ($this->withDdg) {
            $ddgCount = $this->ingestDuckDuckGoReviews($vendor, $profile);
        }

        $console->line(sprintf('  • Stored %d reviews (%d Google, %d open-web)', $googleCount + $ddgCount, $googleCount, $ddgCount));

        return [
            'total' => $googleCount + $ddgCount,
            'google' => $googleCount,
            'ddg' => $ddgCount,
        ];
    }

    private function buildProviderProfile(VendorDetail $vendor): ?array
    {
        $vendorName = $this->norm($vendor->vendor_name);
        if (!$vendorName) {
            return null;
        }

        $user = $vendor->user;
        $firstName = $this->norm($user->first_name ?? $user->name ?? '');
        $lastName = $this->norm($user->last_name ?? '');

        $city = $this->norm($vendor->city ?? $vendor->town ?? $user->city ?? $this->extractCityFromLocation($user->location_name ?? $user->location ?? ''));
        $postcode = $this->norm($vendor->postcode ?? $user->postcode ?? '');
        $country = $this->norm($vendor->country ?? $user->country ?? 'UK');

        $productTitles = $vendor->products
            ->pluck('title')
            ->map(fn ($title) => $this->norm($title))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'provider_id' => (string) $vendor->id,
            'vendor_name' => $vendorName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'city' => $city,
            'postcode' => $postcode,
            'country' => $country ?: 'UK',
            'product_titles' => array_slice($productTitles, 0, 8),
            'location' => $this->norm(implode(', ', array_filter([$city, $postcode, $country]))),
        ];
    }

    private function buildProviderQueries(array $provider): array
    {
        $queries = [];
        $vendor = $provider['vendor_name'];
        $fullName = $this->norm(trim(($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? '')));
        $loc = $provider['location'] ?? '';
        $products = array_slice($provider['product_titles'] ?? [], 0, 3);

        $this->pushQuery($queries, [$vendor, $fullName, $loc]);
        $this->pushQuery($queries, [$vendor, $loc]);
        $this->pushQuery($queries, [$vendor, 'reviews', $loc]);
        $this->pushQuery($queries, [$vendor, 'reviews']);

        foreach ($products as $title) {
            $this->pushQuery($queries, [$vendor, $title, $loc]);
            $this->pushQuery($queries, [$vendor, $title]);
            $this->pushQuery($queries, [$title, $loc]);
        }

        if (strlen($vendor) < 4 && $fullName) {
            $this->pushQuery($queries, [$fullName, $loc]);
            foreach ($products as $title) {
                $this->pushQuery($queries, [$fullName, $title, $loc]);
                $this->pushQuery($queries, [$fullName, $title]);
            }
        }

        return $queries;
    }

    private function pushQuery(array &$queries, array $parts): void
    {
        $query = $this->norm(implode(' ', array_filter($parts)));
        if (!$query) {
            return;
        }

        foreach ($queries as $existing) {
            if (strcasecmp($existing, $query) === 0) {
                return;
            }
        }

        $queries[] = $query;
    }

    private function findBestPlace(ArtisanCommand $console, array $provider, array $queries): ?array
    {
        $bestPick = null;
        $bestCandidates = [];
        $bestQuery = null;

        foreach (array_slice($queries, 0, 12) as $query) {
            $search = $this->placesTextSearch($query);
            $candidates = array_slice($search['results'] ?? [], 0, 6);
            if (!$candidates) {
                continue;
            }

            $pick = $this->geminiPickCandidate($provider, $query, $candidates);

            if (!empty($pick['best_place_id']) && ($pick['confidence'] ?? 0) >= 0.6) {
                return [
                    'place_id' => $pick['best_place_id'],
                    'confidence' => (float) ($pick['confidence'] ?? 0),
                    'reason' => $pick['reason'] ?? '',
                    'query' => $query,
                    'candidates' => $candidates,
                ];
            }

            if (!$bestPick || (($pick['confidence'] ?? 0) > ($bestPick['confidence'] ?? 0))) {
                $bestPick = $pick;
                $bestQuery = $query;
                $bestCandidates = $candidates;
            }
        }

        if ($bestPick && !empty($bestPick['best_place_id'])) {
            return [
                'place_id' => $bestPick['best_place_id'],
                'confidence' => (float) ($bestPick['confidence'] ?? 0),
                'reason' => $bestPick['reason'] ?? '',
                'query' => $bestQuery,
                'candidates' => $bestCandidates,
            ];
        }

        if ($bestQuery) {
            $fallback = $this->placesTextSearch($bestQuery);
            $placeId = $fallback['results'][0]['place_id'] ?? null;
            if ($placeId) {
                return [
                    'place_id' => $placeId,
                    'confidence' => max((float) ($bestPick['confidence'] ?? 0), 0.25),
                    'reason' => $bestPick['reason'] ?? 'Fallback to top Places result (Gemini did not confirm a match)',
                    'query' => $bestQuery,
                    'candidates' => array_slice($fallback['results'] ?? [], 0, 6),
                ];
            }
        }

        return null;
    }

    private function placesTextSearch(string $query): array
    {
        try {
            $response = Http::retry(2, 500)
                ->timeout(30)
                ->get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
                    'query' => $query,
                    'region' => $this->mapsRegion,
                    'key' => $this->mapsKey,
                ]);

            return $response->successful() ? $response->json() : [];
        } catch (Throwable) {
            return [];
        }
    }

    private function placesDetails(string $placeId): array
    {
        try {
            $response = Http::retry(2, 500)
                ->timeout(30)
                ->get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'place_id' => $placeId,
                    'fields' => implode(',', [
                        'place_id',
                        'name',
                        'formatted_address',
                        'rating',
                        'user_ratings_total',
                        'url',
                        'website',
                        'formatted_phone_number',
                        'reviews',
                    ]),
                    'key' => $this->mapsKey,
                ]);

            return $response->successful() ? $response->json() : [];
        } catch (Throwable) {
            return [];
        }
    }

    private function geminiPickCandidate(array $provider, string $query, array $candidates): array
    {
        if (!$this->geminiKey) {
            return ['best_place_id' => null, 'confidence' => 0, 'reason' => 'Gemini disabled'];
        }

        $payload = [
            'provider' => [
                'id' => $provider['provider_id'],
                'vendor_name' => $provider['vendor_name'],
                'person_name' => $this->norm(trim(($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? ''))),
                'city' => $provider['city'] ?? null,
                'postcode' => $provider['postcode'] ?? null,
                'country' => $provider['country'] ?? null,
                'product_titles' => array_slice($provider['product_titles'] ?? [], 0, 6),
            ],
            'query_used' => $query,
            'candidates' => collect($candidates)
                ->take(6)
                ->map(fn ($c) => [
                    'place_id' => $c['place_id'] ?? null,
                    'name' => $c['name'] ?? null,
                    'formatted_address' => $c['formatted_address'] ?? null,
                    'rating' => $c['rating'] ?? null,
                    'user_ratings_total' => $c['user_ratings_total'] ?? null,
                ])->values()->all(),
        ];

        $prompt = <<<PROMPT
You are an entity-resolution assistant.
Pick the single best matching Google Places candidate for the provider.

Return ONLY strict JSON:
{
  "best_place_id": string|null,
  "confidence": number (0..1),
  "reason": string
}

Rules:
- If none match, best_place_id must be null.
- Prefer candidates whose name/address match the provider's vendor name OR relate strongly to the provider's product titles.
- If the candidate looks like a different company/person, reject it.

Data:
%s
PROMPT;

        $prompt = sprintf($prompt, json_encode($payload, JSON_PRETTY_PRINT));

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $this->geminiModel,
            urlencode($this->geminiKey)
        );

        try {
            $response = Http::timeout(45)
                ->acceptJson()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [['text' => $prompt]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                    ],
                ], ['key' => $this->geminiKey]);

            if (!$response->successful()) {
                return ['best_place_id' => null, 'confidence' => 0, 'reason' => 'Gemini HTTP error'];
            }

            $json = $response->json();
            $text = Arr::get($json, 'candidates.0.content.parts.0.text') ?? Arr::get($json, 'text', '');
            $obj = $this->extractJsonObject($text);
            if (!$obj) {
                return ['best_place_id' => null, 'confidence' => 0, 'reason' => 'Gemini returned unparsable JSON'];
            }

            return [
                'best_place_id' => $obj['best_place_id'] ?? null,
                'confidence' => isset($obj['confidence']) ? (float) $obj['confidence'] : 0,
                'reason' => $obj['reason'] ?? '',
            ];
        } catch (Throwable) {
            return ['best_place_id' => null, 'confidence' => 0, 'reason' => 'Gemini request failed'];
        }
    }

    private function extractJsonObject(?string $text): ?array
    {
        $text = (string) $text;
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $slice = substr($text, $start, $end - $start + 1);
        $decoded = json_decode($slice, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function ingestGoogleReviews(VendorDetail $vendor, array $profile, array $match): int
    {
        $details = $this->placesDetails($match['place_id']);
        $place = $details['result'] ?? [];
        $reviews = $place['reviews'] ?? [];
        if (!$reviews) {
            return 0;
        }

        $count = 0;
        foreach ($reviews as $review) {
            $normalized = $this->normalizeGoogleReview($review);
            if (!$normalized['text']) {
                continue;
            }

            $externalId = sha1(implode('|', [
                $match['place_id'],
                $normalized['author'] ?? '',
                $normalized['time'] ?? '',
                $normalized['text'],
            ]));

            $count += $this->storeReview($vendor, $profile, [
                'source' => 'google_places',
                'external_id' => $externalId,
                'source_url' => $place['url'] ?? null,
                'rating' => $normalized['rating'],
                'review_text' => $normalized['text'],
                'reviewer_name' => $normalized['author'],
                'reviewed_at' => $normalized['time'],
                'confidence' => $match['confidence'] ?? null,
                'match_reason' => $match['reason'] ?? null,
                'query_used' => $match['query'] ?? null,
                'place_id' => $place['place_id'] ?? $match['place_id'],
                'place_payload' => [
                    'place_id' => $place['place_id'] ?? $match['place_id'],
                    'name' => $place['name'] ?? null,
                    'formatted_address' => $place['formatted_address'] ?? null,
                    'rating' => $place['rating'] ?? null,
                    'user_ratings_total' => $place['user_ratings_total'] ?? null,
                    'website' => $place['website'] ?? null,
                    'phone' => $place['formatted_phone_number'] ?? null,
                    'maps_url' => $place['url'] ?? null,
                ],
                'raw_payload' => $review,
            ]);
        }

        return $count;
    }

    private function normalizeGoogleReview(array $review): array
    {
        return [
            'author' => $review['author_name'] ?? $review['authorName'] ?? $review['author'] ?? null,
            'rating' => isset($review['rating']) ? (float) $review['rating'] : null,
            'text' => $review['text'] ?? $review['review_text'] ?? $review['reviewBody'] ?? null,
            'time' => $review['time'] ?? $review['time_created'] ?? null,
        ];
    }

    private function ingestDuckDuckGoReviews(VendorDetail $vendor, array $profile): int
    {
        $queries = $this->buildDuckDuckGoQueries($profile, 12);
        if (empty($queries)) {
            return 0;
        }

        $count = 0;
        foreach ($queries as $query) {
            $results = $this->duckDuckGoSearch($query, 6);
            foreach ($results as $result) {
                if (!$this->isAllowedReviewUrl($result['url'])) {
                    continue;
                }

                $html = $this->fetchHtml($result['url']);
                if (!$html) {
                    continue;
                }

                $extracted = $this->extractReviewsFromJsonLd($html);
                foreach (array_slice($extracted, 0, 10) as $review) {
                    $externalId = sha1(implode('|', [
                        $result['url'],
                        $review['reviewer_name'] ?? '',
                        $review['review_date'] ?? '',
                        $review['review_text'] ?? '',
                    ]));

                    $count += $this->storeReview($vendor, $profile, [
                        'source' => 'open_web_jsonld',
                        'external_id' => $externalId,
                        'source_url' => $result['url'],
                        'rating' => $review['rating'] ?? null,
                        'review_text' => $review['review_text'] ?? null,
                        'reviewer_name' => $review['reviewer_name'] ?? null,
                        'reviewed_at' => $review['review_date'] ?? null,
                        'confidence' => 0.3,
                        'match_reason' => sprintf('Extracted from JSON-LD review markup (query: %s)', $query),
                        'query_used' => $query,
                        'place_id' => null,
                        'place_payload' => null,
                        'raw_payload' => $review,
                    ]);
                }
            }
        }

        return $count;
    }

    private function buildDuckDuckGoQueries(array $profile, int $max = 12): array
    {
        $queries = [];
        $variants = $this->buildCompanyVariants($profile);
        if (empty($variants)) {
            return [];
        }

        $primary = $variants[0];
        $phrase = $this->quotePhrase($primary);
        $city = $this->norm($profile['city'] ?? '');
        $location = $this->norm($profile['location'] ?? '');
        $products = array_slice($profile['product_titles'] ?? [], 0, 2);
        $personName = $this->norm(trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')));

        $this->pushQuery($queries, [$phrase, 'reviews']);
        $this->pushQuery($queries, [$phrase, '"customer reviews"']);
        $this->pushQuery($queries, [$phrase, 'testimonials']);
        if ($city) {
            $this->pushQuery($queries, [$phrase, 'reviews', $city]);
        }
        if ($location && (!$city || stripos($location, $city) === false)) {
            $this->pushQuery($queries, [$phrase, 'reviews', $location]);
        }

        foreach ($products as $title) {
            $this->pushQuery($queries, [$phrase, $this->quotePhrase($title), 'reviews']);
        }

        $siteTargets = [
            'trustpilot.com',
            'google.com/maps',
            'facebook.com',
            'yell.com',
            'checkatrade.com',
            'feefo.com',
            'reviews.io',
            'tripadvisor.co.uk',
        ];

        foreach ($siteTargets as $site) {
            $this->pushQuery($queries, [$phrase, "site:$site"]);
        }

        foreach (['complaints', 'scam', 'refund', 'problems'] as $keyword) {
            $this->pushQuery($queries, [$phrase, $keyword]);
        }

        $this->pushQuery($queries, [$phrase, 'intitle:review']);
        $this->pushQuery($queries, [$phrase, 'inurl:review']);
        $this->pushQuery($queries, [$phrase, 'inurl:reviews']);
        $this->pushQuery($queries, [$phrase, '(inurl:review OR inurl:reviews)']);

        foreach (array_slice($variants, 1, 2) as $variant) {
            $variantPhrase = $this->quotePhrase($variant);
            $this->pushQuery($queries, [$variantPhrase, 'reviews']);
            if ($city) {
                $this->pushQuery($queries, [$variantPhrase, 'reviews', $city]);
            }
        }

        if ($personName) {
            $personPhrase = $this->quotePhrase($personName);
            $this->pushQuery($queries, [$phrase, $personPhrase, 'reviews']);
            if ($city) {
                $this->pushQuery($queries, [$phrase, $personPhrase, 'reviews', $city]);
            }
        }

        $this->pushQuery($queries, [$phrase, '"Google reviews"']);
        $this->pushQuery($queries, [$phrase, 'site:google.com/maps']);

        return array_slice($queries, 0, $max);
    }

    private function buildCompanyVariants(array $profile): array
    {
        $base = $this->norm($profile['vendor_name'] ?? '');
        if (!$base) {
            return [];
        }

        $variants = [$base];

        $stripped = preg_replace('/\b(limited|ltd|uk)\b\.?/i', '', $base);
        $stripped = $this->norm($stripped);
        if ($stripped && strcasecmp($stripped, $base) !== 0) {
            $variants[] = $stripped;
        }

        if (!preg_match('/\bltd\.?$/i', $base)) {
            $variants[] = $this->norm($base . ' Ltd');
        }
        if (!preg_match('/\blimited$/i', $base)) {
            $variants[] = $this->norm($base . ' Limited');
        }
        if (!preg_match('/\buk$/i', $base)) {
            $variants[] = $this->norm($base . ' UK');
        }

        return $this->uniqueStrings($variants);
    }

    private function quotePhrase(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return "\"" . addcslashes($value, '"') . "\"";
    }

    private function uniqueStrings(array $values): array
    {
        $seen = [];
        $out = [];
        foreach ($values as $value) {
            $value = $this->norm($value);
            if (!$value) {
                continue;
            }
            $key = strtolower($value);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $value;
        }

        return $out;
    }

    private function duckDuckGoSearch(string $query, int $max): array
    {
        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'User-Agent' => self::DEFAULT_USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get('https://html.duckduckgo.com/html/', [
                    'q' => $query,
                ]);

            if (!$response->successful()) {
                return [];
            }

            return $this->parseDuckDuckGoResults($response->body(), $max);
        } catch (Throwable) {
            return [];
        }
    }

    private function parseDuckDuckGoResults(string $html, int $max): array
    {
        $results = [];
        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        if (!$dom->loadHTML($html)) {
            libxml_clear_errors();
            libxml_use_internal_errors((bool) $previous);
            return [];
        }
        libxml_clear_errors();
        libxml_use_internal_errors((bool) $previous);
        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query("//a[contains(@class,'result__a')]");
        foreach ($nodes as $node) {
            $href = $node->getAttribute('href');
            $decodedUrl = $this->decodeDuckDuckGoUrl($href);
            $title = trim($node->textContent ?? '');
            if ($decodedUrl) {
                $results[] = ['title' => $title, 'url' => $decodedUrl];
            }
            if (count($results) >= $max) {
                break;
            }
        }

        return $results;
    }

    private function decodeDuckDuckGoUrl(string $href): string
    {
        $href = trim($href);
        if (!$href) {
            return '';
        }

        if (Str::startsWith($href, '/l/?uddg=')) {
            $href = substr($href, 9);
        } elseif (Str::startsWith($href, 'https://duckduckgo.com/l/?uddg=')) {
            $href = substr($href, strlen('https://duckduckgo.com/l/?uddg='));
        }

        $decoded = urldecode($href);
        if (Str::startsWith($decoded, '//')) {
            $decoded = 'https:' . $decoded;
        }

        return $decoded;
    }

    private function fetchHtml(string $url): ?string
    {
        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'User-Agent' => self::DEFAULT_USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $contentType = $response->header('content-type');
            if ($contentType && !Str::contains(strtolower($contentType), 'text/html')) {
                return null;
            }

            return $response->body();
        } catch (Throwable) {
            return null;
        }
    }

    private function extractReviewsFromJsonLd(string $html): array
    {
        $pattern = '/<script[^>]*type=["\']application\\/ld\+json["\'][^>]*>(.*?)<\\/script>/is';
        preg_match_all($pattern, $html, $matches);
        $reviews = [];

        foreach ($matches[1] as $block) {
            $json = trim($block);
            if (!$json) {
                continue;
            }

            $decoded = json_decode($json, true);
            if (!$decoded) {
                continue;
            }

            $nodes = $this->flattenJsonLdNodes($decoded);
            foreach ($nodes as $node) {
                $reviewNodes = $node['review'] ?? $node['reviews'] ?? null;
                if (!$reviewNodes) {
                    continue;
                }
                $list = $this->asList($reviewNodes);
                foreach ($list as $r) {
                    $text = $r['reviewBody'] ?? $r['description'] ?? null;
                    if (!$text || strlen(trim($text)) < 10) {
                        continue;
                    }
                    $rating = $r['reviewRating']['ratingValue'] ?? $r['ratingValue'] ?? null;
                    $reviews[] = [
                        'reviewer_name' => $r['author']['name'] ?? $r['author'] ?? null,
                        'rating' => $rating !== null ? (float) $rating : null,
                        'review_text' => trim($text),
                        'review_date' => $r['datePublished'] ?? null,
                    ];
                }
            }
        }

        return $reviews;
    }

    private function flattenJsonLdNodes($data): array
    {
        if (is_array($data) && isset($data['@graph']) && is_array($data['@graph'])) {
            return $data['@graph'];
        }

        if (is_array($data) && Arr::isAssoc($data)) {
            return [$data];
        }

        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    private function asList($value): array
    {
        if (is_array($value)) {
            return Arr::isAssoc($value) ? [$value] : $value;
        }

        return $value ? [$value] : [];
    }

    private function isAllowedReviewUrl(string $url): bool
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        if (!$host) {
            return false;
        }

        foreach (self::REVIEW_HOST_KEYWORDS as $keyword) {
            if (str_contains($host, $keyword)) {
                if ($keyword === 'google') {
                    return str_contains($url, '/maps');
                }
                return true;
            }
        }

        return false;
    }

    private function storeReview(VendorDetail $vendor, array $profile, array $data): int
    {
        if (empty($data['review_text'])) {
            return 0;
        }

        $reviewedAt = $this->normalizeDateTime($data['reviewed_at'] ?? null);

        $payload = [
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->vendor_name,
            'provider_identifier' => $profile['provider_id'],
            'source' => $data['source'],
            'external_id' => $data['external_id'],
            'source_url' => $data['source_url'] ?? null,
            'rating' => $data['rating'] !== null ? (float) $data['rating'] : null,
            'review_text' => $data['review_text'],
            'reviewer_name' => $data['reviewer_name'] ?? null,
            'reviewed_at' => $reviewedAt,
            'confidence' => $data['confidence'] ?? null,
            'match_reason' => $data['match_reason'] ?? null,
            'query_used' => $data['query_used'] ?? null,
            'product_titles' => $profile['product_titles'],
            'place_id' => $data['place_id'] ?? null,
            'place_payload' => $data['place_payload'] ?? null,
            'raw_payload' => $data['raw_payload'] ?? null,
        ];

        if ($this->dryRun) {
            return 1;
        }

        $model = VendorReview::updateOrCreate(
            [
                'source' => $data['source'],
                'external_id' => $data['external_id'],
            ],
            $payload
        );

        return ($model->wasRecentlyCreated || $model->wasChanged()) ? 1 : 0;
    }

    private function normalizeDateTime($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    private function extractCityFromLocation(?string $location): string
    {
        if (!$location) {
            return '';
        }

        $parts = array_map('trim', explode(',', $location));
        return $parts[0] ?? '';
    }

    private function norm(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? '';
        return trim($value);
    }
}
