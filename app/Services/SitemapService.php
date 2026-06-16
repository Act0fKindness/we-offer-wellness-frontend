<?php

namespace App\Services;

use App\Models\OfferingV3;
use App\Models\PageRedirect;
use App\Models\Product;
use App\Models\User;
use App\Support\WowEventsFeed;
use App\Services\WhatCategoryCacheService;
use Carbon\Carbon;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SitemapService
{
    private const MAX_URLS_PER_FILE = 50000;

    private const CANONICAL_FORMATS = [
        'therapies',
        'classes',
        'events',
        'workshops',
        'retreats',
    ];

    private const GROUP_ORDER = [
        'static',
        'types',
        'modalities',
        'near-me',
        'offerings',
        'locations',
        'online',
        'by-need',
        'practitioners',
        'guides',
    ];

    private const ALWAYS_EMIT_EMPTY_SEGMENTS = [
        'guides',
    ];

    private const NEED_SLUGS = [
        'stress-and-anxiety',
        'sleep-issues',
        'low-mood-burnout',
        'overwhelm',
        'worry',
        'pain-management',
        'mens-wellbeing',
        'digestive-health',
        'fertility-pregnancy',
        'nervous-system',
        'breathwork',
        'guided-meditation',
        'corporate-wellbeing',
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

    private const LEGACY_CITIES = [
        'london',
        'manchester',
        'birmingham',
        'leeds',
        'bristol',
        'brighton',
        'liverpool',
        'glasgow',
        'edinburgh',
        'cardiff',
        'kent',
    ];

    private const LEGACY_TYPES = [
        'therapies',
        'events',
        'workshops',
        'classes',
        'retreats',
        'gifts',
    ];

    private ?Collection $liveProducts = null;

    private ?Collection $liveOfferings = null;

    private ?array $locationCatalog = null;

    private ?array $locationPathIndex = null;

    private ?array $whatCategories = null;

    private ?array $eventsCache = null;

    private ?array $segmentFilesCache = null;

    private ?array $manifestEntriesCache = null;

    private ?array $submissionUrlsCache = null;

    private ?array $redirectPathMatchers = null;

    private ?array $redirectExactPaths = null;

    private ?array $redirectSourcePatterns = null;

    private ?array $sitemapRouteCache = null;

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

        $expectedFilenames = array_values(array_unique(array_map(
            static fn (array $file): string => (string) ($file['filename'] ?? ''),
            $files
        )));

        foreach (File::files($outputDirectory) as $existingFile) {
            if (strtolower((string) $existingFile->getExtension()) !== 'xml') {
                continue;
            }

            if (!in_array($existingFile->getFilename(), $expectedFilenames, true)) {
                File::delete($existingFile->getPathname());
            }
        }

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
            'file_count' => count($files),
            'total_urls' => array_sum(array_map(
                static fn (array $file): int => (int) ($file['count'] ?? 0),
                $files
            )),
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
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];
        foreach ($this->manifestEntries() as $entry) {
            $lines[] = '  <sitemap>';
            $lines[] = '    <loc>' . $this->escapeXml((string) ($entry['url'] ?? '')) . '</loc>';
            $lines[] = '    <lastmod>' . $this->escapeXml((string) ($entry['lastmod'] ?? now()->toAtomString())) . '</lastmod>';
            $lines[] = '  </sitemap>';
        }
        $lines[] = '</sitemapindex>';

        return implode("\n", $lines);
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
            if ($entries === [] && ! in_array($segment, self::ALWAYS_EMIT_EMPTY_SEGMENTS, true)) {
                continue;
            }

            $chunks = $entries === [] ? [[]] : array_chunk($entries, self::MAX_URLS_PER_FILE);
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
     * @return array<int, string>
     */
    public function canonicalUrls(): array
    {
        $urls = [];

        foreach ($this->buildSegmentGroups() as $entries) {
            foreach ($this->normalizeEntries($entries) as $entry) {
                $loc = trim((string) ($entry['loc'] ?? ''));
                if ($loc === '') {
                    continue;
                }

                $urls[$loc] = true;
            }
        }

        ksort($urls);

        return array_keys($urls);
    }

    /**
     * @return array<string, array<int, array{loc:string,lastmod:string}>>
     */
    private function buildSegmentGroups(): array
    {
        return [
            'static' => $this->buildStaticEntries(),
            'types' => $this->buildTypeEntries(),
            'modalities' => $this->buildModalityEntries(),
            'near-me' => $this->buildNearMeEntries(),
            'offerings' => $this->buildOfferingEntries(),
            'locations' => $this->buildLocationEntries(),
            'online' => $this->buildOnlineEntries(),
            'by-need' => $this->buildByNeedEntries(),
            'practitioners' => $this->buildPractitionerEntries(),
            'guides' => $this->buildGuideEntries(),
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
            '/giftcards',
            '/mindful-times',
            '/partners',
            '/plan',
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
        $latestByUrl = [];
        $seo = app(SeoStructureService::class);

        $rememberLatest = function (string $url, mixed $value) use (&$latestByUrl): void {
            $atom = $this->dateToAtom($value);

            if (!isset($latestByUrl[$url])) {
                $latestByUrl[$url] = $atom;
                return;
            }

            try {
                $current = Carbon::parse($latestByUrl[$url])->getTimestamp();
                $candidate = Carbon::parse($atom)->getTimestamp();

                if ($candidate > $current) {
                    $latestByUrl[$url] = $atom;
                }
            } catch (\Throwable $e) {
                $latestByUrl[$url] = $atom;
            }
        };

        foreach ($this->liveProducts()->filter(fn (Product $product): bool => $product->category !== null) as $product) {
            $format = $seo->inferFormatKeyFromProduct($product);
            $modality = $seo->inferModalitySlugFromProduct($product);
            if ($modality === '') {
                continue;
            }

            $url = $seo->modalityPageUrl($format, $modality);
            $rememberLatest($url, $product->updated_at ?? null);
        }

        foreach ($this->liveOfferings()->filter(fn (OfferingV3 $offering): bool => $offering->category !== null) as $offering) {
            $format = $seo->inferFormatKeyFromOffering($offering);
            $modality = $seo->inferModalitySlugFromOffering($offering);
            if ($modality === '') {
                continue;
            }

            $url = $seo->modalityPageUrl($format, $modality);
            $rememberLatest($url, $offering->updated_at ?? null);
        }

        foreach ($latestByUrl as $url => $lastmod) {
            $this->addEntry($entries, $url, $lastmod);
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildTypeEntries(): array
    {
        $entries = [];
        $latestByUrl = [];
        $seo = app(SeoStructureService::class);

        $rememberLatest = function (string $url, mixed $value) use (&$latestByUrl): void {
            $atom = $this->dateToAtom($value);

            if (!isset($latestByUrl[$url])) {
                $latestByUrl[$url] = $atom;
                return;
            }

            try {
                $current = Carbon::parse($latestByUrl[$url])->getTimestamp();
                $candidate = Carbon::parse($atom)->getTimestamp();

                if ($candidate > $current) {
                    $latestByUrl[$url] = $atom;
                }
            } catch (\Throwable $e) {
                $latestByUrl[$url] = $atom;
            }
        };

        foreach ($this->liveProducts()->filter(fn (Product $product): bool => $product->category !== null) as $product) {
            $format = $seo->inferFormatKeyFromProduct($product);
            $rememberLatest($seo->formatPageUrl($format), $product->updated_at ?? null);
        }

        foreach ($this->liveOfferings()->filter(fn (OfferingV3 $offering): bool => $offering->category !== null) as $offering) {
            $format = $seo->inferFormatKeyFromOffering($offering);
            $rememberLatest($seo->formatPageUrl($format), $offering->updated_at ?? null);
        }

        foreach ($latestByUrl as $url => $lastmod) {
            $this->addEntry($entries, $url, $lastmod);
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
        $latestByUrl = [];
        $seo = app(SeoStructureService::class);

        $rememberLatest = function (string $url, mixed $value) use (&$latestByUrl): void {
            $atom = $this->dateToAtom($value);

            if (!isset($latestByUrl[$url])) {
                $latestByUrl[$url] = $atom;
                return;
            }

            try {
                $current = Carbon::parse($latestByUrl[$url])->getTimestamp();
                $candidate = Carbon::parse($atom)->getTimestamp();

                if ($candidate > $current) {
                    $latestByUrl[$url] = $atom;
                }
            } catch (\Throwable $e) {
                $latestByUrl[$url] = $atom;
            }
        };

        foreach ($this->liveProducts()->filter(fn (Product $product): bool => $product->category !== null) as $product) {
            $format = $seo->inferFormatKeyFromProduct($product);
            $modality = $seo->inferModalitySlugFromProduct($product);
            if ($modality === '') {
                continue;
            }

            $base = $seo->modalityPageUrl($format, $modality);
            foreach ($this->locationPathsForItem($product) as $locationPath) {
                $suffix = Str::after($locationPath, '/locations');
                $rememberLatest($base . $suffix, $product->updated_at ?? null);
            }
        }

        foreach ($this->liveOfferings()->filter(fn (OfferingV3 $offering): bool => $offering->category !== null) as $offering) {
            $format = $seo->inferFormatKeyFromOffering($offering);
            $modality = $seo->inferModalitySlugFromOffering($offering);
            if ($modality === '') {
                continue;
            }

            $base = $seo->modalityPageUrl($format, $modality);
            foreach ($this->locationPathsForItem($offering) as $locationPath) {
                $suffix = Str::after($locationPath, '/locations');
                $rememberLatest($base . $suffix, $offering->updated_at ?? null);
            }
        }

        foreach ($latestByUrl as $url => $lastmod) {
            $this->addEntry($entries, $url, $lastmod);
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildOnlineEntries(): array
    {
        $entries = [];
        $latestByUrl = [];
        $seo = app(SeoStructureService::class);

        $rememberLatest = function (string $url, mixed $value) use (&$latestByUrl): void {
            $atom = $this->dateToAtom($value);

            if (!isset($latestByUrl[$url])) {
                $latestByUrl[$url] = $atom;
                return;
            }

            try {
                $current = Carbon::parse($latestByUrl[$url])->getTimestamp();
                $candidate = Carbon::parse($atom)->getTimestamp();

                if ($candidate > $current) {
                    $latestByUrl[$url] = $atom;
                }
            } catch (\Throwable $e) {
                $latestByUrl[$url] = $atom;
            }
        };

        $rememberLatest(url('/online'), now()->toAtomString());

        foreach ($this->liveProducts()->filter(fn (Product $product): bool => $product->category !== null) as $product) {
            $locations = method_exists($product, 'getLocations') ? (array) $product->getLocations() : [];
            $hasOnline = in_array('Online', $locations, true) || in_array('online', array_map('strtolower', $locations), true);
            if (!$hasOnline) {
                continue;
            }

            $modality = $seo->inferModalitySlugFromProduct($product);
            if ($modality === '') {
                continue;
            }

            $rememberLatest(url('/online/' . $modality), $product->updated_at ?? null);
        }

        foreach ($this->liveOfferings()->filter(fn (OfferingV3 $offering): bool => $offering->category !== null) as $offering) {
            $locations = method_exists($offering, 'getLocations') ? (array) $offering->getLocations() : [];
            $hasOnline = in_array('Online', $locations, true) || in_array('online', array_map('strtolower', $locations), true);
            if (!$hasOnline) {
                continue;
            }

            $modality = $seo->inferModalitySlugFromOffering($offering);
            if ($modality !== '') {
                $rememberLatest(url('/online/' . $modality), $offering->updated_at ?? null);
            }

            $canonical = $seo->canonicalOfferingUrl($offering);
            if (str_starts_with($this->normalizeSitemapPath($canonical), '/online/')) {
                $rememberLatest($canonical, $offering->updated_at ?? null);
            }
        }

        foreach ($latestByUrl as $url => $lastmod) {
            $this->addEntry($entries, $url, $lastmod);
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildByNeedEntries(): array
    {
        $entries = [];
        $latestByUrl = [];
        $needHits = array_fill_keys(self::NEED_SLUGS, now()->toAtomString());

        foreach ($this->liveProducts() as $product) {
            $needs = array_values(array_filter(array_map(
                static fn ($value): string => trim((string) $value),
                (array) data_get($product, 'by_need', [])
            )));

            if ($needs === []) {
                continue;
            }

            $updated = $this->dateToAtom($product->updated_at ?? null);
            foreach ($needs as $needSlug) {
                if (!isset($needHits[$needSlug])) {
                    continue;
                }

                try {
                    $current = Carbon::parse($needHits[$needSlug])->getTimestamp();
                    $candidate = Carbon::parse($updated)->getTimestamp();

                    if ($candidate > $current) {
                        $needHits[$needSlug] = $updated;
                    }
                } catch (\Throwable $e) {
                    $needHits[$needSlug] = $updated;
                }
            }
        }

        $latestByUrl[url('/needs')] = now()->toAtomString();

        foreach ($needHits as $slug => $lastmod) {
            $latestByUrl[url('/needs/' . $slug)] = $lastmod;
        }

        foreach ($latestByUrl as $url => $lastmod) {
            $this->addEntry($entries, $url, $lastmod);
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildOfferingEntries(): array
    {
        $entries = [];
        $latestByUrl = [];
        $seo = app(SeoStructureService::class);

        $rememberLatest = function (string $url, mixed $value) use (&$latestByUrl): void {
            $atom = $this->dateToAtom($value);

            if (!isset($latestByUrl[$url])) {
                $latestByUrl[$url] = $atom;
                return;
            }

            try {
                $current = Carbon::parse($latestByUrl[$url])->getTimestamp();
                $candidate = Carbon::parse($atom)->getTimestamp();

                if ($candidate > $current) {
                    $latestByUrl[$url] = $atom;
                }
            } catch (\Throwable $e) {
                $latestByUrl[$url] = $atom;
            }
        };

        foreach ($this->liveProducts() as $product) {
            $rememberLatest($seo->canonicalProductUrl($product), $product->updated_at ?? null);
        }

        foreach ($this->liveOfferings() as $offering) {
            $rememberLatest($seo->canonicalOfferingUrl($offering), $offering->updated_at ?? null);
        }

        foreach ($latestByUrl as $url => $lastmod) {
            $this->addEntry($entries, $url, $lastmod);
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
            if ($url === '' || ! str_starts_with($this->normalizeSitemapPath($url), '/practioner/')) {
                continue;
            }

            $this->addEntry($entries, $url, $this->dateToAtom($user->updated_at ?? null));
        }

        return array_values($entries);
    }

    /**
     * @return array<int, array{loc:string,lastmod:string}>
     */
    private function buildGuideEntries(): array
    {
        return array_values(app(GuideRegistryService::class)->publishedGuideEntries());
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
            ->select(['id', 'title', 'product_type', 'tags_list', 'updated_at', 'category_id', 'product_status_id', 'vendor_id'])
            ->with([
                'category:id,name',
                'options.values',
                'vendor.locations',
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
            ->select(['id', 'title', 'updated_at', 'status', 'category_id', 'type_id', 'vendor_id'])
            ->with(['category:id,name', 'vendor.locations'])
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

    private function inferFormatFromSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return 'therapies';
        }

        if (str_contains($slug, 'class') || str_contains($slug, 'yoga') || str_contains($slug, 'pilates')) {
            return 'classes';
        }

        if (str_contains($slug, 'workshop')) {
            return 'workshops';
        }

        if (str_contains($slug, 'retreat')) {
            return 'retreats';
        }

        if (
            str_contains($slug, 'event')
            || str_contains($slug, 'festival')
            || str_contains($slug, 'gong')
            || str_contains($slug, 'bath')
            || str_contains($slug, 'circle')
            || str_contains($slug, 'ceremony')
        ) {
            return 'events';
        }

        return 'therapies';
    }

    /**
     * @return array<int, string>
     */
    private function locationPathsForItem(mixed $item): array
    {
        $paths = [];
        $index = $this->locationPathIndex();

        $vendorLocations = collect(data_get($item, 'vendor.locations', []));
        foreach ($vendorLocations as $location) {
            $countrySlug = $this->normalizeCountrySlug((string) data_get($location, 'country', 'United Kingdom'));
            $countySlug = $this->normalizeLocationSegment((string) (data_get($location, 'county') ?: data_get($location, 'region') ?: ''));
            $townSlug = $this->normalizeLocationSegment((string) data_get($location, 'city', ''));

            foreach ([
                $countrySlug . '|' . $countySlug . '|' . $townSlug,
                $countrySlug . '|' . $countySlug . '|',
                $countrySlug . '||',
            ] as $key) {
                $path = $index[$key] ?? null;
                if (!is_string($path) || $path === '') {
                    continue;
                }

                $paths[] = $path;
                foreach ($this->ancestorLocationPaths($path) as $ancestorPath) {
                    $paths[] = $ancestorPath;
                }
            }
        }

        $paths = array_values(array_filter(array_unique($paths), static fn (string $path): bool => str_starts_with($path, '/locations/')));
        sort($paths);

        return $paths;
    }

    /**
     * @return array<string, string>
     */
    private function locationPathIndex(): array
    {
        if ($this->locationPathIndex !== null) {
            return $this->locationPathIndex;
        }

        $index = [];
        foreach ((array) data_get($this->locationCatalog(), 'countries', []) as $country) {
            $countrySlug = $this->normalizeLocationSegment((string) data_get($country, 'slug', ''));
            if ($countrySlug !== '' && !empty($country['path'])) {
                $index[$countrySlug . '||'] = (string) $country['path'];
            }

            foreach ((array) data_get($country, 'counties', []) as $county) {
                $countySlug = $this->normalizeLocationSegment((string) data_get($county, 'slug', ''));
                if ($countrySlug !== '' && $countySlug !== '' && !empty($county['path'])) {
                    $index[$countrySlug . '|' . $countySlug . '|'] = (string) $county['path'];
                }

                foreach ((array) data_get($county, 'towns', []) as $town) {
                    $path = (string) data_get($town, 'path', '');
                    if ($path === '') {
                        continue;
                    }

                    $segments = explode('/', trim(str_replace('/locations/', '', $path), '/'));
                    $townCountry = $this->normalizeLocationSegment((string) ($segments[0] ?? ''));
                    $townCounty = $this->normalizeLocationSegment((string) ($segments[1] ?? ''));
                    $townSlug = $this->normalizeLocationSegment((string) ($segments[2] ?? ''));
                    if ($townCountry !== '' && $townCounty !== '' && $townSlug !== '') {
                        $index[$townCountry . '|' . $townCounty . '|' . $townSlug] = $path;
                    }
                }
            }
        }

        return $this->locationPathIndex = $index;
    }

    /**
     * @return array<int, string>
     */
    private function ancestorLocationPaths(string $path): array
    {
        $path = trim($path);
        if (!str_starts_with($path, '/locations/')) {
            return [];
        }

        $segments = array_values(array_filter(explode('/', trim(Str::after($path, '/locations/'), '/'))));
        $paths = [];

        if (count($segments) >= 3) {
            $paths[] = '/locations/' . $segments[0] . '/' . $segments[1];
        }

        if (count($segments) >= 2) {
            $paths[] = '/locations/' . $segments[0];
        }

        return array_values(array_unique($paths));
    }

    private function normalizeCountrySlug(string $country): string
    {
        $country = strtolower(trim($country));
        if ($country === '' || in_array($country, ['uk', 'u.k.', 'united kingdom', 'great britain', 'england', 'scotland', 'wales', 'northern ireland'], true)) {
            return 'united-kingdom';
        }

        return Str::slug($country);
    }

    private function normalizeLocationSegment(string $value): string
    {
        return Str::slug(trim($value));
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
            if (! $this->shouldIncludeUrl($loc)) {
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
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];
        foreach ($entries as $entry) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . $this->escapeXml((string) ($entry['loc'] ?? '')) . '</loc>';
            $lines[] = '    <lastmod>' . $this->escapeXml((string) ($entry['lastmod'] ?? now()->toAtomString())) . '</lastmod>';
            $lines[] = '  </url>';
        }
        $lines[] = '</urlset>';

        return implode("\n", $lines);
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
        if (! $this->shouldIncludeUrl($loc)) {
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

    /**
     * @return array<int, string>
     */
    private function redirectPathMatchers(): array
    {
        if ($this->redirectSourcePatterns !== null) {
            return $this->redirectSourcePatterns;
        }

        $patterns = PageRedirect::query()
            ->where('is_active', true)
            ->pluck('from_path')
            ->map(fn ($path): string => $this->normalizeSitemapPath((string) $path))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $exactPaths = [];
        foreach ($patterns as $pattern) {
            if (! str_contains($pattern, '{')) {
                $exactPaths[$pattern] = true;
            }
        }

        $this->redirectExactPaths = $exactPaths;

        return $this->redirectSourcePatterns = $patterns;
    }

    private function redirectPatternMatchesPath(string $path, string $pattern): bool
    {
        $path = $this->normalizeSitemapPath($path);
        $pattern = $this->normalizeSitemapPath($pattern);

        if ($path === '' || $pattern === '') {
            return false;
        }

        if (! str_contains($pattern, '{')) {
            return $path === $pattern;
        }

        $quoted = preg_quote($pattern, '~');
        $regex = preg_replace('~\\\\\{([A-Za-z0-9_]+)\\\\\}~', '(?P<$1>[^/]+)', $quoted);
        if (! is_string($regex) || $regex === '') {
            return false;
        }

        if (! preg_match('~^' . $regex . '/?$~i', $path, $matches)) {
            return false;
        }

        if (isset($matches['city']) && ! in_array(strtolower((string) $matches['city']), self::LEGACY_CITIES, true)) {
            return false;
        }

        if (isset($matches['type']) && ! in_array(strtolower((string) $matches['type']), self::LEGACY_TYPES, true)) {
            return false;
        }

        return true;
    }

    private function normalizeSitemapPath(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $path = parse_url($value, PHP_URL_PATH);
        $path = $path !== null && $path !== false ? trim((string) $path) : trim($value);

        if ($path === '') {
            return '';
        }

        if (! str_starts_with($path, '/')) {
            $path = '/' . ltrim($path, '/');
        }

        return rtrim($path, '/') ?: '/';
    }

    private function shouldIncludeUrl(string $loc): bool
    {
        $loc = trim($loc);
        if ($loc === '' || str_contains($loc, '?') || str_contains($loc, '#')) {
            return false;
        }

        $path = $this->normalizeSitemapPath($loc);
        if ($path === '') {
            return false;
        }

        if ($this->redirectExactPaths === null && $this->redirectPathMatchers === null) {
            $this->redirectPathMatchers();
        }

        if (isset($this->redirectExactPaths[$path])) {
            return false;
        }

        foreach ($this->redirectPathMatchers() as $pattern) {
            if ($this->redirectPatternMatchesPath($path, (string) $pattern)) {
                return false;
            }
        }

        if (! $this->routeResolves($path)) {
            return false;
        }

        if (! $this->routeReturnsNon404($path)) {
            return false;
        }

        return true;
    }

    private function routeResolves(string $path): bool
    {
        $path = $this->normalizeSitemapPath($path);
        if ($path === '') {
            return false;
        }

        if ($this->sitemapRouteCache === null) {
            $this->sitemapRouteCache = [];
        }

        if ($this->sitemapRouteCache !== null && array_key_exists($path, $this->sitemapRouteCache)) {
            return (bool) $this->sitemapRouteCache[$path];
        }

        try {
            app('router')->getRoutes()->match(Request::create($path, 'GET'));
            return $this->sitemapRouteCache[$path] = true;
        } catch (\Throwable $e) {
            return $this->sitemapRouteCache[$path] = false;
        }
    }

    private function routeReturnsNon404(string $path): bool
    {
        $path = $this->normalizeSitemapPath($path);
        if ($path === '') {
            return false;
        }

        if ($this->sitemapRouteCache === null) {
            $this->sitemapRouteCache = [];
        }

        if ($this->sitemapRouteCache !== null && array_key_exists('status:' . $path, $this->sitemapRouteCache)) {
            return (bool) $this->sitemapRouteCache['status:' . $path];
        }

        $request = Request::create($path, 'GET');

        try {
            $kernel = app(HttpKernel::class);
            $response = $kernel->handle($request);
            $status = (int) $response->getStatusCode();

            if (method_exists($kernel, 'terminate')) {
                $kernel->terminate($request, $response);
            }

            return $this->sitemapRouteCache['status:' . $path] = $status >= 200 && $status < 300;
        } catch (\Throwable $e) {
            return $this->sitemapRouteCache['status:' . $path] = false;
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
