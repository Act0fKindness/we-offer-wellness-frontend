<?php

namespace App\Services;

use App\Models\OfferingV3;
use App\Models\Product;
use App\Models\User;
use App\Support\WowEventsFeed;
use App\Services\WhatCategoryCacheService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SitemapService
{
    private const MAX_URLS_PER_FILE = 50000;

    private const GROUP_ORDER = [
        'static',
        'modalities',
        'types',
        'locations',
        'near-me',
        'modality-location',
        'type-location',
        'offerings',
        'events',
        'practitioners',
    ];

    private const RESERVED_CATEGORY_SLUGS = [
        'about',
        'cart',
        'checkout',
        'contact',
        'cookies',
        'corporate',
        'corporate-wellbeing',
        'corporate-wellness',
        'dashboard',
        'event',
        'events',
        'experience',
        'experiences',
        'gift',
        'gift-cards',
        'gift-vouchers',
        'giftcards',
        'help',
        'locations',
        'mindful-times',
        'near-me',
        'online',
        'online-near-me',
        'offerings',
        'partners',
        'plan',
        'privacy',
        'providers',
        'refunds-and-cancellations',
        'reviews',
        'safety-and-contraindications',
        'search',
        'sitemap',
        'terms',
        'therapy',
        'therapies',
        'v3',
        'workshop',
        'workshops',
        'class',
        'classes',
        'retreat',
        'retreats',
        'needs',
    ];

    private ?Collection $liveProducts = null;

    private ?Collection $liveOfferings = null;

    private ?array $locationCatalog = null;

    private ?array $whatCategories = null;

    private ?array $eventsCache = null;

    private ?array $segmentFilesCache = null;

    private ?array $manifestEntriesCache = null;

    private ?array $submissionUrlsCache = null;

    public function outputDirectory(): string
    {
        return public_path('sitemaps');
    }

    public function manifestPath(): string
    {
        return $this->outputDirectory() . '/manifest.json';
    }

    public function buildAndWriteAll(?string $outputDirectory = null): array
    {
        $outputDirectory = $outputDirectory ?: $this->outputDirectory();
        File::ensureDirectoryExists($outputDirectory);
        File::ensureDirectoryExists(dirname($this->manifestPath()));

        $files = $this->buildSitemapFiles();
        foreach ($files as $file) {
            File::put($outputDirectory . '/' . $file['filename'], $file['xml']);
        }

        $indexXml = $this->buildIndexXml();
        File::put(public_path('sitemap.xml'), $indexXml);
        File::put(public_path('sitemap-index.xml'), $indexXml);

        $staticXml = $this->buildSegmentXml('static');
        if ($staticXml !== null) {
            File::put(public_path('sitemap-pages.xml'), $staticXml);
        }

        $manifest = [
            'generated_at' => now()->toAtomString(),
            'index_url' => url('/sitemap.xml'),
            'submission_urls' => $this->submissionUrlsFromFiles($files),
            'sitemaps' => $this->manifestEntries(),
        ];

        File::put(
            $this->manifestPath(),
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return [
            'files' => $files,
            'manifest' => $manifest,
            'index_xml' => $indexXml,
        ];
    }

    public function buildIndexXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($this->manifestEntries() as $entry) {
            $xml .= '<sitemap>'
                . '<loc>' . $this->escapeXml((string) ($entry['url'] ?? '')) . '</loc>'
                . '<lastmod>' . $this->escapeXml((string) ($entry['lastmod'] ?? now()->toAtomString())) . '</lastmod>'
                . '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    public function buildSegmentXml(string $segment): ?string
    {
        $file = collect($this->buildSitemapFiles())
            ->first(fn (array $candidate): bool => (string) ($candidate['segment'] ?? '') === $segment || (string) ($candidate['filename'] ?? '') === $segment . '.xml');

        if ($file === null) {
            return null;
        }

        return (string) ($file['xml'] ?? '');
    }

    public function submissionUrls(): array
    {
        if ($this->submissionUrlsCache !== null) {
            return $this->submissionUrlsCache;
        }

        if (File::isFile($this->manifestPath())) {
            try {
                $decoded = json_decode((string) File::get($this->manifestPath()), true);
                $urls = array_values(array_filter(array_map(
                    static fn ($url): string => trim((string) $url),
                    (array) data_get($decoded, 'submission_urls', [])
                )));

                if ($urls !== []) {
                    return $this->submissionUrlsCache = array_values(array_unique($urls));
                }
            } catch (\Throwable $e) {
                // Fall back to config-defined URLs below.
            }
        }

        $raw = trim((string) config('services.search_console.sitemap_urls', ''));
        if ($raw === '') {
            $fallback = trim((string) config('services.search_console.sitemap_url', ''));
            $raw = $fallback;
        }

        if ($raw === '') {
            return $this->submissionUrlsCache = [];
        }

        return $this->submissionUrlsCache = collect(explode(',', $raw))
            ->map(fn (string $url): string => trim($url))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{filename:string,segment:string,url:string,lastmod:string,count:int,xml:string}>
     */
    public function buildSitemapFiles(): array
    {
        if ($this->segmentFilesCache !== null) {
            return $this->segmentFilesCache;
        }

        $files = [];
        foreach ($this->buildSegmentGroups() as $segment => $entries) {
            $entries = $this->normalizeEntries($entries);
            if ($entries === []) {
                continue;
            }

            $chunks = array_chunk($entries, self::MAX_URLS_PER_FILE);
            foreach ($chunks as $index => $chunk) {
                $filename = $this->segmentFilename($segment, $index);
                $files[] = [
                    'filename' => $filename,
                    'segment' => pathinfo($filename, PATHINFO_FILENAME),
                    'url' => url('/sitemaps/' . $filename),
                    'lastmod' => $this->chunkLastMod($chunk),
                    'count' => count($chunk),
                    'xml' => $this->renderUrlsetXml($chunk),
                ];
            }
        }

        return $this->segmentFilesCache = $files;
    }

    /**
     * @return array<int, array{name:string,url:string,lastmod:string,count:int}>
     */
    public function manifestEntries(): array
    {
        if ($this->manifestEntriesCache !== null) {
            return $this->manifestEntriesCache;
        }

        $entries = [];

        foreach ($this->buildSitemapFiles() as $file) {
            $entries[] = [
                'name' => (string) $file['segment'],
                'url' => (string) $file['url'],
                'lastmod' => (string) $file['lastmod'],
                'count' => (int) $file['count'],
            ];
        }

        return $this->manifestEntriesCache = $entries;
    }

    /**
     * @return array<string, array<int, array{loc:string,lastmod:string}>>
     */
    private function buildSegmentGroups(): array
    {
        return [
            'static' => $this->buildStaticEntries(),
            'modalities' => $this->buildModalityEntries(),
            'types' => $this->buildTypeEntries(),
            'locations' => $this->buildLocationEntries(),
            'near-me' => $this->buildNearMeEntries(),
            'modality-location' => $this->buildModalityLocationEntries(),
            'type-location' => $this->buildTypeLocationEntries(),
            'offerings' => $this->buildOfferingEntries(),
            'events' => $this->buildEventsEntries(),
            'practitioners' => $this->buildPractitionerEntries(),
        ];
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildStaticEntries(): array
    {
        $now = now()->toAtomString();
        $entries = [];

        foreach ([
            '/',
            '/about',
            '/contact',
            '/help',
            '/help/faq',
            '/help/gift-cards',
            '/offerings',
            '/giftcards',
            '/mindful-times',
            '/partners',
            '/plan',
            '/needs',
            '/reviews',
            '/privacy',
            '/terms',
            '/cookies',
            '/refunds-and-cancellations',
            '/safety-and-contraindications',
            '/corporate',
            '/holistic-therapies-uk',
            '/corporate/wellbeing-workshops',
            '/corporate/meditation',
            '/corporate/breathwork',
            '/corporate/sound-bath',
            '/corporate/gift-vouchers',
            '/corporate/employee-rewards',
        ] as $path) {
            $this->addEntry($entries, url($path), $now);
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildModalityEntries(): array
    {
        $entries = [];

        $latestBySlug = [];
        $rememberLatest = function (string $slug, mixed $value) use (&$latestBySlug): void {
            $atom = $this->dateToAtom($value);

            if (!isset($latestBySlug[$slug])) {
                $latestBySlug[$slug] = $atom;
                return;
            }

            try {
                $current = Carbon::parse($latestBySlug[$slug])->getTimestamp();
                $candidate = Carbon::parse($atom)->getTimestamp();

                if ($candidate > $current) {
                    $latestBySlug[$slug] = $atom;
                }
            } catch (\Throwable $e) {
                $latestBySlug[$slug] = $atom;
            }
        };

        foreach ($this->liveProducts()->filter(fn (Product $product): bool => $product->category !== null) as $product) {
            $slug = $this->categorySlug($product->category?->name);
            if ($slug === '' || $this->isReservedCategorySlug($slug)) {
                continue;
            }

            $rememberLatest($slug, $product->updated_at ?? null);
        }

        foreach ($this->liveOfferings()->filter(fn (OfferingV3 $offering): bool => $offering->category !== null) as $offering) {
            $slug = $this->categorySlug($offering->category?->name);
            if ($slug === '' || $this->isReservedCategorySlug($slug)) {
                continue;
            }

            $rememberLatest($slug, $offering->updated_at ?? null);
        }

        foreach ((array) data_get(app(WhatCategoryCacheService::class)->load(), 'categories', []) as $category) {
            $slug = $this->categorySlug((string) ($category['slug'] ?? ''));
            if ($slug === '' || $this->isReservedCategorySlug($slug)) {
                continue;
            }

            if ((int) data_get($category, 'counts.total', 0) <= 0) {
                continue;
            }

            $this->addEntry($entries, url('/' . $slug), $latestBySlug[$slug] ?? now()->toAtomString());
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildTypeEntries(): array
    {
        $entries = [];
        $now = now()->toAtomString();

        foreach ([
            '/therapies',
            '/online',
            '/events',
            '/workshops',
            '/classes',
            '/retreats',
            '/gifts',
        ] as $path) {
            $this->addEntry($entries, url($path), $now);
        }

        foreach ($this->liveProducts()->filter(fn (Product $product): bool => $product->category !== null) as $product) {
            $categorySlug = $this->categorySlug($product->category?->name);
            if ($categorySlug === '' || $this->isReservedCategorySlug($categorySlug)) {
                continue;
            }

            $typeSegment = $this->typeSegmentFromProduct($product);
            $this->addEntry(
                $entries,
                url('/' . $categorySlug . '/' . $typeSegment),
                $this->dateToAtom($product->updated_at ?? null)
            );
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildLocationEntries(): array
    {
        $entries = [];
        $catalog = $this->locationCatalog();

        $this->addEntry($entries, url('/locations'), now()->toAtomString());

        foreach ((array) data_get($catalog, 'countries', []) as $country) {
            if (!empty($country['online'])) {
                continue;
            }

            if ((int) data_get($country, 'counts.total', 0) > 0 && !empty($country['path'])) {
                $this->addEntry($entries, url((string) $country['path']), now()->toAtomString());
            }

            foreach ((array) data_get($country, 'counties', []) as $county) {
                if ((int) data_get($county, 'counts.total', 0) > 0 && !empty($county['path'])) {
                    $this->addEntry($entries, url((string) $county['path']), now()->toAtomString());
                }

                foreach ((array) data_get($county, 'towns', []) as $town) {
                    if ((int) data_get($town, 'counts.total', 0) > 0 && !empty($town['path'])) {
                        $this->addEntry($entries, url((string) $town['path']), now()->toAtomString());
                    }
                }
            }
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildNearMeEntries(): array
    {
        $entries = [];
        $now = now()->toAtomString();

        foreach ([
            '/near-me',
            '/online-near-me',
            '/reiki-near-me',
            '/sound-healing-near-me',
            '/holistic-therapy-near-me',
            '/wellness-classes-near-me',
        ] as $path) {
            $this->addEntry($entries, url($path), $now);
        }

        foreach ($this->nearMeCategorySlugs() as $slug) {
            $this->addEntry($entries, url('/' . $slug . '-near-me'), $now);
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildModalityLocationEntries(): array
    {
        $entries = [];
        $catalog = $this->locationCatalog();
        $locationPaths = $this->canonicalLocationPaths($catalog);

        foreach ($this->nearMeCategorySlugs() as $slug) {
            foreach ($locationPaths as $locationPath) {
                $suffix = Str::after($locationPath, '/locations');
                if ($suffix === $locationPath) {
                    continue;
                }

                $this->addEntry($entries, url('/' . $slug . '-near-me' . $suffix), now()->toAtomString());
            }
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildTypeLocationEntries(): array
    {
        $entries = [];

        foreach ($this->liveProducts()->filter(fn (Product $product): bool => $product->category !== null) as $product) {
            $categorySlug = $this->categorySlug($product->category?->name);
            if ($categorySlug === '' || $this->isReservedCategorySlug($categorySlug)) {
                continue;
            }

            $typeSegment = $this->typeSegmentFromProduct($product);
            foreach ($this->productLocationSlugs($product) as $locationSlug) {
                $this->addEntry(
                    $entries,
                    url('/' . $categorySlug . '/' . $typeSegment . '/' . $locationSlug),
                    $this->dateToAtom($product->updated_at ?? null)
                );
            }
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildOfferingEntries(): array
    {
        $entries = [];

        foreach ($this->liveProducts() as $product) {
            $slug = Str::slug((string) ($product->title ?? '')) ?: (string) $product->id;
            $this->addEntry(
                $entries,
                url('/offerings/' . $product->id . '-' . $slug),
                $this->dateToAtom($product->updated_at ?? null)
            );
        }

        foreach ($this->liveOfferings() as $offering) {
            $slug = Str::slug((string) ($offering->title ?? '')) ?: (string) $offering->id;
            $this->addEntry(
                $entries,
                url('/offerings/' . $offering->id . '-' . $slug),
                $this->dateToAtom($offering->updated_at ?? null)
            );
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildEventsEntries(): array
    {
        $entries = [];
        $this->addEntry($entries, url('/events'), now()->toAtomString());

        foreach ($this->eventItems() as $item) {
            $slug = $this->eventSlug($item);
            if ($slug === '') {
                continue;
            }

            $this->addEntry(
                $entries,
                url('/events/' . $slug),
                $this->dateToAtom(data_get($item, 'updated_at') ?? data_get($item, 'published_at') ?? data_get($item, 'date'))
            );
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildPractitionerEntries(): array
    {
        $entries = [];

        foreach ($this->publicProfiles() as $user) {
            $url = trim((string) ($user->practitioner_profile_url ?? ''));
            if ($url === '') {
                continue;
            }

            $this->addEntry($entries, $url, $this->dateToAtom($user->updated_at ?? null));
        }

        return array_values($entries);
    }

    /**
     * @return Collection<int, Product>
     */
    private function liveProducts(): Collection
    {
        if ($this->liveProducts !== null) {
            return $this->liveProducts;
        }

        return $this->liveProducts = Product::query()
            ->select(['id', 'title', 'product_type', 'tags_list', 'updated_at', 'category_id', 'product_status_id'])
            ->with([
                'category:id,name',
                'options.values',
            ])
            ->where(function ($query): void {
                $query->whereHas('status', function ($status): void {
                    $status->whereIn('status', ['live', 'approved']);
                })->orWhereNull('product_status_id');
            })
            ->get();
    }

    /**
     * @return Collection<int, OfferingV3>
     */
    private function liveOfferings(): Collection
    {
        if ($this->liveOfferings !== null) {
            return $this->liveOfferings;
        }

        return $this->liveOfferings = OfferingV3::query()
            ->select(['id', 'title', 'updated_at', 'status', 'category_id', 'type_id'])
            ->with(['category:id,name'])
            ->whereIn('status', ['live', 'approved'])
            ->get();
    }

    private function locationCatalog(): array
    {
        if ($this->locationCatalog !== null) {
            return $this->locationCatalog;
        }

        return $this->locationCatalog = app(LocationCatalogService::class)->load();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function nearMeCategorySlugs(): array
    {
        if ($this->whatCategories !== null) {
            return $this->whatCategories;
        }

        $categories = [];
        foreach ((array) data_get(app(WhatCategoryCacheService::class)->load(), 'categories', []) as $category) {
            $slug = $this->categorySlug((string) ($category['slug'] ?? ''));
            if ($slug === '' || $this->isReservedCategorySlug($slug)) {
                continue;
            }

            if ((int) data_get($category, 'counts.total', 0) <= 0) {
                continue;
            }

            $categories[] = $slug;
        }

        foreach (['reiki', 'sound-healing', 'holistic-therapy', 'wellness-classes'] as $slug) {
            if (!in_array($slug, $categories, true)) {
                $categories[] = $slug;
            }
        }

        sort($categories);

        return $this->whatCategories = array_values(array_unique($categories));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function eventItems(): array
    {
        if ($this->eventsCache !== null) {
            return $this->eventsCache;
        }

        $items = [];
        $page = 1;
        $perPage = 100;
        $guard = 0;

        while ($guard < 20) {
            $guard++;
            $results = WowEventsFeed::list([
                'offering_kind' => 'event',
                'page' => $page,
                'per_page' => $perPage,
            ]);

            if (!($results['ok'] ?? false)) {
                break;
            }

            $batch = array_values(array_filter((array) ($results['items'] ?? []), 'is_array'));
            if ($batch === []) {
                break;
            }

            $items = array_merge($items, $batch);

            $meta = (array) ($results['meta'] ?? []);
            $lastPage = (int) ($meta['last_page'] ?? $meta['total_pages'] ?? 0);
            $currentPage = (int) ($meta['current_page'] ?? $page);

            if ($lastPage > 0 && $currentPage >= $lastPage) {
                break;
            }

            if (count($batch) < $perPage) {
                break;
            }

            $page++;
        }

        $unique = [];
        foreach ($items as $item) {
            $slug = $this->eventSlug($item);
            if ($slug === '') {
                continue;
            }

            $unique[$slug] = $item;
        }

        ksort($unique);

        return $this->eventsCache = array_values($unique);
    }

    /**
     * @return Collection<int, User>
     */
    private function publicProfiles(): Collection
    {
        return User::query()
            ->with('roles')
            ->where(function ($query): void {
                $query->where('is_vendor', true)
                    ->orWhereHas('roles', function ($roles): void {
                        $roles->whereRaw('LOWER(name) = ?', ['provider']);
                    })
                    ->orWhereHas('roles', function ($roles): void {
                        $roles->whereRaw('LOWER(name) = ?', ['admin']);
                    });
            })
            ->get();
    }

    /**
     * @param array<string, mixed> $entries
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function normalizeEntries(array $entries): array
    {
        $normalised = [];
        foreach ($entries as $entry) {
            $loc = trim((string) ($entry['loc'] ?? ''));
            if ($loc === '') {
                continue;
            }

            $normalised[$loc] = [
                'loc' => $loc,
                'lastmod' => $this->dateToAtom($entry['lastmod'] ?? null),
            ];
        }

        ksort($normalised);

        return array_values($normalised);
    }

    /**
     * @param array<int, array{loc:string,lastmod:string}> $entries
     */
    private function renderUrlsetXml(array $entries): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($entries as $entry) {
            $xml .= '<url>'
                . '<loc>' . $this->escapeXml((string) ($entry['loc'] ?? '')) . '</loc>'
                . '<lastmod>' . $this->escapeXml((string) ($entry['lastmod'] ?? now()->toAtomString())) . '</lastmod>'
                . '</url>';
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function segmentFilename(string $segment, int $chunkIndex): string
    {
        if ($chunkIndex === 0) {
            return $segment . '.xml';
        }

        return $segment . '-' . ($chunkIndex + 1) . '.xml';
    }

    private function addEntry(array &$entries, string $loc, ?string $lastmod = null): void
    {
        $loc = trim($loc);
        if ($loc === '') {
            return;
        }

        $entries[$loc] = [
            'loc' => $loc,
            'lastmod' => $lastmod ?: now()->toAtomString(),
        ];
    }

    private function chunkLastMod(array $entries): string
    {
        $timestamps = [];
        foreach ($entries as $entry) {
            $value = trim((string) ($entry['lastmod'] ?? ''));
            if ($value === '') {
                continue;
            }

            try {
                $timestamps[] = Carbon::parse($value)->getTimestamp();
            } catch (\Throwable $e) {
                continue;
            }
        }

        if ($timestamps === []) {
            return now()->toAtomString();
        }

        return Carbon::createFromTimestamp(max($timestamps), 'UTC')->toAtomString();
    }

    private function dateToAtom(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toAtomString();
        }

        $value = trim((string) $value);
        if ($value === '') {
            return now()->toAtomString();
        }

        try {
            return Carbon::parse($value)->toAtomString();
        } catch (\Throwable $e) {
            return now()->toAtomString();
        }
    }

    private function eventSlug(array $item): string
    {
        foreach (['slug', 'handle'] as $key) {
            $candidate = trim((string) ($item[$key] ?? ''));
            if ($candidate !== '') {
                return trim(basename(parse_url($candidate, PHP_URL_PATH) ?: $candidate), '/');
            }
        }

        $url = trim((string) ($item['url'] ?? $item['path'] ?? ''));
        if ($url !== '') {
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            $path = trim((string) $path, '/');
            if ($path !== '') {
                return trim((string) basename($path), '/');
            }
        }

        return '';
    }

    private function productLocationSlugs(Product $product): array
    {
        $locations = [];
        foreach ((array) $product->getLocations() as $location) {
            $label = trim((string) $location);
            if ($label === '') {
                continue;
            }

            $slug = Str::slug($label);
            if ($slug === '') {
                continue;
            }

            $locations[] = $slug;
        }

        return array_values(array_unique($locations));
    }

    private function typeSegmentFromProduct(Product $product): string
    {
        $productType = strtolower(trim((string) $product->product_type));
        $tags = strtolower(trim((string) $product->tags_list));

        if (str_contains($productType, 'workshop')) {
            return 'workshops';
        }
        if (str_contains($productType, 'event')) {
            return 'events';
        }
        if (str_contains($productType, 'class')) {
            return 'classes';
        }
        if (str_contains($productType, 'retreat')) {
            return 'retreats';
        }
        if (str_contains($productType, 'gift') || str_contains($tags, 'gift')) {
            return 'gifts';
        }

        return 'therapies';
    }

    private function categorySlug(?string $value): string
    {
        return Str::slug(trim((string) $value));
    }

    private function canonicalLocationPaths(array $catalog): array
    {
        $paths = [];

        foreach ((array) data_get($catalog, 'countries', []) as $country) {
            if (!empty($country['online'])) {
                continue;
            }

            foreach ((array) data_get($country, 'counties', []) as $county) {
                if ((int) data_get($county, 'counts.total', 0) <= 0) {
                    continue;
                }

                if (!empty($county['path'])) {
                    $paths[] = (string) $county['path'];
                }

                foreach ((array) data_get($county, 'towns', []) as $town) {
                    if ((int) data_get($town, 'counts.total', 0) > 0 && !empty($town['path'])) {
                        $paths[] = (string) $town['path'];
                    }
                }
            }

            if ((int) data_get($country, 'counts.total', 0) > 0 && !empty($country['path'])) {
                $paths[] = (string) $country['path'];
            }
        }

        $paths = array_values(array_filter(array_unique($paths), static fn (string $path): bool => str_starts_with($path, '/locations/')));
        sort($paths);

        return $paths;
    }

    private function isReservedCategorySlug(string $slug): bool
    {
        return in_array($slug, self::RESERVED_CATEGORY_SLUGS, true);
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param array<int, array{filename:string,segment:string,url:string,lastmod:string,count:int,xml:string}> $files
     * @return array<int, string>
     */
    private function submissionUrlsFromFiles(array $files): array
    {
        $urls = [url('/sitemap.xml')];
        foreach ($files as $file) {
            $urls[] = (string) ($file['url'] ?? '');
        }

        $urls = array_values(array_filter(array_unique(array_map('trim', $urls))));

        return $urls;
    }
}
