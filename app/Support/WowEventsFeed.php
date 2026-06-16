<?php

namespace App\Support;

use App\Models\OfferingDetail;
use App\Models\OfferingV3;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WowEventsFeed
{
    private const DEFAULT_PER_PAGE = 24;

    private const MAX_PER_PAGE = 200;

    /**
     * Fetch a list of events/workshops from local inventory with lightweight caching.
     */
    public static function list(array $query = [], int $ttlMinutes = 3): array
    {
        $query = self::normaliseQuery($query);
        $cacheKey = self::cacheKey('list:' . md5(json_encode($query)));

        $results = self::rememberSafely($cacheKey, now()->addMinutes($ttlMinutes), function () use ($query) {
            $inventory = self::inventory();
            if ($inventory === null) {
                return ['items' => [], 'meta' => [], 'ok' => false];
            }

            $filtered = self::applyFilters($inventory, $query);
            $sorted = self::sortItems($filtered, (string) ($query['sort'] ?? ''));

            $perPage = self::perPage($query);
            $page = max(1, (int) ($query['page'] ?? 1));
            $total = count($sorted);
            $lastPage = max(1, (int) ceil(max(1, $total) / max(1, $perPage)));
            $page = min($page, $lastPage);
            $offset = ($page - 1) * $perPage;

            return [
                'items' => array_values(array_slice($sorted, $offset, $perPage)),
                'meta' => [
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $lastPage,
                    'count' => count(array_slice($sorted, $offset, $perPage)),
                ],
                'ok' => true,
            ];
        });

        if (!is_array($results)) {
            return ['items' => [], 'meta' => [], 'ok' => false];
        }

        return $results;
    }

    /**
     * Find a single event/workshop by slug.
     */
    public static function find(string $slug, int $ttlMinutes = 10): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $cacheKey = self::cacheKey('show:' . md5($slug));

        $result = self::rememberSafely($cacheKey, now()->addMinutes($ttlMinutes), function () use ($slug) {
            $inventory = self::inventory();
            if ($inventory === null || $inventory === []) {
                return null;
            }

            $needle = self::normalizeSlug($slug);
            $bestItem = null;
            $bestScore = PHP_INT_MAX;
            $bestDistance = PHP_INT_MAX;

            foreach ($inventory as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $score = self::slugMatchScore($item, $needle);
                if ($score === null) {
                    continue;
                }

                $distance = self::sortDistance($item);
                if ($score < $bestScore || ($score === $bestScore && $distance < $bestDistance)) {
                    $bestItem = $item;
                    $bestScore = $score;
                    $bestDistance = $distance;
                }
            }

            return $bestItem;
        });

        return is_array($result) ? $result : null;
    }

    /**
     * Determine whether a given filter set has any inventory.
     */
    public static function hasInventory(array $query = [], int $ttlMinutes = 3): ?bool
    {
        $query = self::normaliseQuery($query);
        $query['per_page'] = max(1, (int) ($query['per_page'] ?? 1));

        $results = self::list($query, $ttlMinutes);
        if (!($results['ok'] ?? false)) {
            return null;
        }

        $items = $results['items'] ?? [];
        if (is_array($items) && count($items) > 0) {
            return true;
        }

        $meta = $results['meta'] ?? [];
        foreach (['total', 'total_count', 'total_items', 'count'] as $key) {
            if (isset($meta[$key]) && is_numeric($meta[$key])) {
                return ((int) $meta[$key]) > 0;
            }
        }

        return false;
    }

    private static function inventory(): ?array
    {
        $cacheKey = self::cacheKey('inventory:v2');

        $inventory = self::rememberSafely($cacheKey, now()->addMinutes(5), function () {
            try {
                $products = Product::query()
                    ->with(['vendor.user', 'vendor.tiers', 'vendor.customerReviews', 'category', 'media', 'options.values'])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->whereHas('status', function ($status): void {
                        $status->whereIn('status', ['live', 'approved']);
                    })
                    ->get();

                $offerings = OfferingV3::query()
                    ->with(['vendor.user', 'vendor.tiers', 'vendor.customerReviews', 'type', 'category', 'media', 'coverMedia'])
                    ->whereIn('status', ['live', 'approved'])
                    ->get();

                $vendorIds = collect($products)
                    ->pluck('vendor_id')
                    ->merge(collect($offerings)->pluck('vendor_id'))
                    ->filter(fn ($value): bool => is_numeric($value) && (int) $value > 0)
                    ->map(fn ($value): int => (int) $value)
                    ->unique()
                    ->values()
                    ->all();

                $vendorReviewMap = self::buildVendorReviewMap($vendorIds);
                $offeringDetailsMap = self::buildOfferingDetailsMap(
                    collect($offerings)
                        ->pluck('id')
                        ->filter(fn ($value): bool => is_numeric($value) && (int) $value > 0)
                        ->map(fn ($value): int => (int) $value)
                        ->values()
                        ->all()
                );

                $items = [];
                foreach ($products as $product) {
                    $item = self::decorateProduct($product, $vendorReviewMap);
                    if ($item !== null) {
                        $items[] = $item;
                    }
                }

                foreach ($offerings as $offering) {
                    $item = self::decorateOffering($offering, $offeringDetailsMap, $vendorReviewMap);
                    if ($item !== null) {
                        $items[] = $item;
                    }
                }

                return $items;
            } catch (\Throwable $e) {
                return null;
            }
        });

        return is_array($inventory) ? $inventory : null;
    }

    /**
     * @param array<int, int> $vendorIds
     * @return array<int, array{count:int,rating:float|null}>
     */
    private static function buildVendorReviewMap(array $vendorIds): array
    {
        if ($vendorIds === []) {
            return [];
        }

        try {
            $rows = DB::table('reviews')
                ->selectRaw('vendor_id, COUNT(*) as review_count, AVG(rating) as avg_rating')
                ->whereIn('vendor_id', $vendorIds)
                ->whereRaw("TRIM(COALESCE(review_text, '')) <> ''")
                ->groupBy('vendor_id')
                ->get();

            $map = [];
            foreach ($rows as $row) {
                $vendorId = (int) ($row->vendor_id ?? 0);
                if ($vendorId <= 0) {
                    continue;
                }

                $rating = isset($row->avg_rating) && is_numeric($row->avg_rating)
                    ? round((float) $row->avg_rating, 1)
                    : null;

                $map[$vendorId] = [
                    'count' => (int) ($row->review_count ?? 0),
                    'rating' => $rating,
                ];
            }

            return $map;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @param array<int, int> $offeringIds
     * @return array<int, OfferingDetail>
     */
    private static function buildOfferingDetailsMap(array $offeringIds): array
    {
        if ($offeringIds === []) {
            return [];
        }

        try {
            return OfferingDetail::query()
                ->whereIn('offering_id', $offeringIds)
                ->get()
                ->keyBy('offering_id')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function decorateProduct(Product $product, array $vendorReviewMap): ?array
    {
        $startAt = EventListing::startAt($product);
        $endAt = EventListing::endAt($product, $startAt);
        if (!$startAt && !$endAt) {
            return null;
        }

        $timezone = EventListing::timezone($product);
        $startAt = $startAt ?: $endAt?->copy();
        $endAt = $endAt ?: $startAt?->copy();
        if (!$startAt) {
            return null;
        }

        if ($endAt && $endAt->lt($startAt)) {
            [$startAt, $endAt] = [$endAt, $startAt];
        }

        $kind = self::eventKind(
            (string) ($product->title ?? ''),
            (string) ($product->product_type ?? ''),
            (string) data_get($product, 'category.name', ''),
            (string) ($product->summary ?? '')
        );

        $sourceSlug = trim((string) ($product->handle ?? $product->title ?? $product->id ?? ''));
        $canonicalSlug = self::canonicalSlug('product', (int) $product->id, $sourceSlug);
        $locations = self::normaliseLocations(method_exists($product, 'getLocations') ? (array) $product->getLocations() : []);
        $locationLabel = self::primaryLocationLabel($locations);
        $locationSearch = implode(' ', array_filter($locations));
        $vendorName = trim((string) data_get($product, 'vendor.vendor_name', ''));
        $planKey = trim((string) data_get($product, 'vendor.user.plan_key', data_get($product, 'vendor.plan_key', '')));
        $vendorId = (int) ($product->vendor_id ?? 0);
        $vendorSummary = $vendorId > 0 ? ($vendorReviewMap[$vendorId] ?? ['count' => 0, 'rating' => null]) : ['count' => 0, 'rating' => null];
        $productRating = is_numeric($product->reviews_avg_rating ?? null)
            ? round((float) $product->reviews_avg_rating, 1)
            : null;
        $productReviewCount = (int) ($product->reviews_count ?? 0);
        $rating = $productRating ?? $vendorSummary['rating'];
        $reviewCount = $productReviewCount > 0 ? $productReviewCount : (int) ($vendorSummary['count'] ?? 0);
        $bodyHtml = self::richTextFromSource((string) ($product->body_html ?? $product->description ?? ''));
        if ($bodyHtml === '') {
            $bodyHtml = self::richTextFromSource((string) ($product->summary ?? ''));
        }
        $summary = self::plainTextExcerpt((string) ($product->summary ?? $product->description ?? $product->body_html ?? ''), 160);
        $price = self::moneyValue($product->price ?? null);
        $url = app(\App\Services\SeoStructureService::class)->canonicalProductUrl($product);
        $availability = $endAt && $endAt->lt(Carbon::now($timezone))
            ? 'https://schema.org/OutOfStock'
            : 'https://schema.org/InStock';

        return array_filter([
            'source_type' => 'product',
            'source_version' => 'legacy',
            'source_id' => (int) $product->id,
            'title' => (string) ($product->title ?? 'Event'),
            'summary' => $summary,
            'description' => $bodyHtml,
            'body_html' => $bodyHtml,
            'content' => $bodyHtml,
            'image' => trim((string) $product->getFirstImageUrl()),
            'url' => $url,
            'slug' => $canonicalSlug,
            'source_slug' => $sourceSlug,
            'handle' => trim((string) ($product->handle ?? '')),
            'type' => $kind,
            'event_kind' => $kind,
            'product_type' => (string) ($product->product_type ?? ''),
            'category' => trim((string) data_get($product, 'category.name', '')),
            'vendor_name' => $vendorName,
            'plan_key' => $planKey,
            'vendor_rating' => $vendorSummary['rating'] ?? null,
            'vendor_review_count' => (int) ($vendorSummary['count'] ?? 0),
            'rating' => $rating,
            'review_count' => $reviewCount,
            'aggregateRating' => $rating && $reviewCount > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => $rating,
                'reviewCount' => $reviewCount,
            ] : null,
            'provider' => $vendorName !== '' ? [
                '@type' => 'Organization',
                'name' => $vendorName,
            ] : null,
            'price' => $price,
            'price_currency' => 'GBP',
            'currency' => 'GBP',
            'offers' => $price !== null ? [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'GBP',
                'availability' => $availability,
                'url' => $url,
            ] : null,
            'availability' => $availability,
            'availability_label' => $endAt && $endAt->lt(Carbon::now($timezone)) ? 'Past event' : 'Upcoming event',
            'location' => $locationLabel,
            'locations' => $locations,
            'location_search' => $locationSearch,
            'format' => self::formatFromLocations($locations),
            'timezone' => $timezone,
            'date' => $startAt->toDateString(),
            'start_date' => $startAt->toDateString(),
            'start_time' => $startAt->format('H:i'),
            'end_date' => $endAt?->toDateString() ?? $startAt->toDateString(),
            'end_time' => $endAt?->format('H:i') ?? $startAt->format('H:i'),
            'start_timestamp' => $startAt->timestamp,
            'end_timestamp' => $endAt?->timestamp ?? $startAt->timestamp,
            'when' => [
                'type' => $kind,
                'event' => [
                    'type' => $kind,
                    'timezone' => $timezone,
                    'start_date' => $startAt->toDateString(),
                    'start_time' => $startAt->format('H:i'),
                    'end_date' => $endAt?->toDateString() ?? $startAt->toDateString(),
                    'end_time' => $endAt?->format('H:i') ?? $startAt->format('H:i'),
                ],
            ],
            'event' => [
                'type' => $kind,
                'timezone' => $timezone,
                'start_date' => $startAt->toDateString(),
                'start_time' => $startAt->format('H:i'),
                'end_date' => $endAt?->toDateString() ?? $startAt->toDateString(),
                'end_time' => $endAt?->format('H:i') ?? $startAt->format('H:i'),
            ],
            'seo_title' => trim((string) ($product->title ?? 'Event')),
            'seo_description' => $summary,
            'search_blob' => self::searchBlob([
                $product->title ?? '',
                $product->summary ?? '',
                $product->description ?? '',
                $product->body_html ?? '',
                $vendorName,
                $product->product_type ?? '',
                data_get($product, 'category.name', ''),
                $locationSearch,
                $kind,
            ]),
        ]);
    }

    private static function decorateOffering(OfferingV3 $offering, array $offeringDetailsMap, array $vendorReviewMap): ?array
    {
        $startAt = EventListing::startAt($offering);
        $endAt = EventListing::endAt($offering, $startAt);
        if (!$startAt && !$endAt) {
            return null;
        }

        $timezone = EventListing::timezone($offering);
        $startAt = $startAt ?: $endAt?->copy();
        $endAt = $endAt ?: $startAt?->copy();
        if (!$startAt) {
            return null;
        }

        if ($endAt && $endAt->lt($startAt)) {
            [$startAt, $endAt] = [$endAt, $startAt];
        }

        $kind = self::eventKind(
            (string) ($offering->title ?? ''),
            (string) data_get($offering, 'type.name', ''),
            (string) data_get($offering, 'category.name', ''),
            (string) ($offering->summary ?? '')
        );

        $sourceSlug = trim((string) ($offering->slug ?? $offering->title ?? $offering->id ?? ''));
        $canonicalSlug = self::canonicalSlug('offering', (int) $offering->id, $sourceSlug);
        $locations = self::normaliseLocations((array) $offering->getLocations());
        $locationLabel = self::primaryLocationLabel($locations);
        $locationSearch = implode(' ', array_filter($locations));
        $vendorName = trim((string) data_get($offering, 'vendor.vendor_name', ''));
        $planKey = trim((string) data_get($offering, 'vendor.user.plan_key', data_get($offering, 'vendor.plan_key', '')));
        $vendorId = (int) ($offering->vendor_id ?? 0);
        $vendorSummary = $vendorId > 0 ? ($vendorReviewMap[$vendorId] ?? ['count' => 0, 'rating' => null]) : ['count' => 0, 'rating' => null];
        $rating = $vendorSummary['rating'] ?? null;
        $reviewCount = (int) ($vendorSummary['count'] ?? 0);
        $details = $offeringDetailsMap[(int) $offering->id] ?? null;
        $bodySource = trim(implode("\n\n", array_filter([
            (string) ($details?->description ?? ''),
            (string) ($details?->what_to_expect ?? ''),
            (string) ($details?->whats_included ?? ''),
        ])));
        $bodyHtml = self::richTextFromSource($bodySource);
        if ($bodyHtml === '') {
            $bodyHtml = self::richTextFromSource((string) ($offering->summary ?? ''));
        }
        $summary = self::plainTextExcerpt((string) ($offering->summary ?? $bodySource), 160);
        $price = self::moneyValue($offering->price ?? null);
        $url = app(\App\Services\SeoStructureService::class)->canonicalOfferingUrl($offering);
        $availability = $endAt && $endAt->lt(Carbon::now($timezone))
            ? 'https://schema.org/OutOfStock'
            : 'https://schema.org/InStock';
        $eventPayload = is_array($offering->event ?? null) ? $offering->event : [];
        $whenPayload = is_array($offering->when ?? null) ? $offering->when : [];
        if ($whenPayload === []) {
            $whenPayload = [
                'type' => $kind,
                'event' => $eventPayload,
            ];
        }
        if ($eventPayload === []) {
            $eventPayload = [
                'type' => $kind,
                'timezone' => $timezone,
                'start_date' => $startAt->toDateString(),
                'start_time' => $startAt->format('H:i'),
                'end_date' => $endAt?->toDateString() ?? $startAt->toDateString(),
                'end_time' => $endAt?->format('H:i') ?? $startAt->format('H:i'),
            ];
            $whenPayload['event'] = $eventPayload;
        }

        return array_filter([
            'source_type' => 'offering',
            'source_version' => 'v3',
            'source_id' => (int) $offering->id,
            'title' => (string) ($offering->title ?? 'Event'),
            'summary' => $summary,
            'description' => $bodyHtml,
            'body_html' => $bodyHtml,
            'content' => $bodyHtml,
            'image' => trim((string) $offering->getFirstImageUrl()),
            'url' => $url,
            'slug' => $canonicalSlug,
            'source_slug' => $sourceSlug,
            'handle' => trim((string) ($offering->slug ?? '')),
            'type' => $kind,
            'event_kind' => $kind,
            'product_type' => trim((string) data_get($offering, 'type.name', data_get($offering, 'category.name', ''))),
            'category' => trim((string) data_get($offering, 'category.name', '')),
            'vendor_name' => $vendorName,
            'plan_key' => $planKey,
            'vendor_rating' => $rating,
            'vendor_review_count' => $reviewCount,
            'rating' => $rating,
            'review_count' => $reviewCount,
            'aggregateRating' => $rating && $reviewCount > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => $rating,
                'reviewCount' => $reviewCount,
            ] : null,
            'provider' => $vendorName !== '' ? [
                '@type' => 'Organization',
                'name' => $vendorName,
            ] : null,
            'price' => $price,
            'price_currency' => 'GBP',
            'currency' => 'GBP',
            'offers' => $price !== null ? [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'GBP',
                'availability' => $availability,
                'url' => $url,
            ] : null,
            'availability' => $availability,
            'availability_label' => $endAt && $endAt->lt(Carbon::now($timezone)) ? 'Past event' : 'Upcoming event',
            'location' => $locationLabel,
            'locations' => $locations,
            'location_search' => $locationSearch,
            'format' => self::formatFromLocations($locations),
            'timezone' => $timezone,
            'date' => $startAt->toDateString(),
            'start_date' => $startAt->toDateString(),
            'start_time' => $startAt->format('H:i'),
            'end_date' => $endAt?->toDateString() ?? $startAt->toDateString(),
            'end_time' => $endAt?->format('H:i') ?? $startAt->format('H:i'),
            'start_timestamp' => $startAt->timestamp,
            'end_timestamp' => $endAt?->timestamp ?? $startAt->timestamp,
            'when' => $whenPayload,
            'event' => $eventPayload,
            'seo_title' => trim((string) ($offering->title ?? 'Event')),
            'seo_description' => $summary,
            'search_blob' => self::searchBlob([
                $offering->title ?? '',
                $offering->summary ?? '',
                $bodySource,
                $vendorName,
                data_get($offering, 'type.name', ''),
                data_get($offering, 'category.name', ''),
                $locationSearch,
                $kind,
            ]),
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private static function applyFilters(array $items, array $query): array
    {
        $slug = trim((string) ($query['slug'] ?? ''));
        $type = strtolower(trim((string) ($query['type'] ?? ($query['offering_kind'] ?? ''))));
        $format = strtolower(trim((string) ($query['format'] ?? '')));
        $location = strtolower(trim((string) ($query['location'] ?? '')));
        $date = strtolower(trim((string) ($query['date'] ?? '')));

        return array_values(array_filter($items, function (array $item) use ($slug, $type, $format, $location, $date): bool {
            if ($slug !== '' && !self::matchesSlug($item, $slug)) {
                return false;
            }

            if ($type !== '' && !self::matchesType($item, $type)) {
                return false;
            }

            if ($format !== '' && !self::matchesFormat($item, $format)) {
                return false;
            }

            if ($location !== '' && !self::matchesLocation($item, $location)) {
                return false;
            }

            if ($date !== '' && !self::matchesDate($item, $date)) {
                return false;
            }

            return true;
        }));
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private static function sortItems(array $items, string $sort): array
    {
        $sort = strtolower(trim($sort));

        usort($items, function (array $left, array $right) use ($sort): int {
            $leftStart = self::timestampValue($left['start_timestamp'] ?? null);
            $rightStart = self::timestampValue($right['start_timestamp'] ?? null);
            $leftEnd = self::timestampValue($left['end_timestamp'] ?? null);
            $rightEnd = self::timestampValue($right['end_timestamp'] ?? null);

            if ($sort === 'date_asc') {
                if ($leftStart !== $rightStart) {
                    return $leftStart <=> $rightStart;
                }

                return strcasecmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
            }

            if ($sort === 'date_desc') {
                if ($leftStart !== $rightStart) {
                    return $rightStart <=> $leftStart;
                }

                return strcasecmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
            }

            $leftScore = self::sortDistance($left);
            $rightScore = self::sortDistance($right);
            if ($leftScore !== $rightScore) {
                return $leftScore <=> $rightScore;
            }

            $leftPast = $leftEnd > 0 && $leftEnd < Carbon::now()->timestamp;
            $rightPast = $rightEnd > 0 && $rightEnd < Carbon::now()->timestamp;
            if ($leftPast !== $rightPast) {
                return $leftPast <=> $rightPast;
            }

            if ($leftStart !== $rightStart) {
                return $leftStart <=> $rightStart;
            }

            return strcasecmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
        });

        return array_values($items);
    }

    private static function matchesSlug(array $item, string $slug): bool
    {
        $needle = self::normalizeSlug($slug);
        if ($needle === '') {
            return true;
        }

        foreach ([
            $item['slug'] ?? '',
            $item['source_slug'] ?? '',
            $item['handle'] ?? '',
            $item['title'] ?? '',
        ] as $candidate) {
            if (self::normalizeSlug((string) $candidate) === $needle) {
                return true;
            }
        }

        return false;
    }

    private static function matchesType(array $item, string $type): bool
    {
        $kind = strtolower(trim((string) ($item['type'] ?? $item['event_kind'] ?? 'event')));
        $type = strtolower(trim($type));

        if ($type === 'event' || $type === 'events') {
            return $kind !== 'workshop';
        }

        if ($type === 'workshop' || $type === 'workshops') {
            return $kind === 'workshop';
        }

        return self::searchHaystack($item, $type);
    }

    private static function matchesFormat(array $item, string $format): bool
    {
        $itemFormat = strtolower(trim((string) ($item['format'] ?? '')));
        $format = strtolower(trim($format));

        if ($format === 'online') {
            return in_array($itemFormat, ['online', 'mixed'], true);
        }

        if ($format === 'in_person' || $format === 'in-person' || $format === 'near_me') {
            return in_array($itemFormat, ['in_person', 'mixed'], true);
        }

        return $itemFormat === $format;
    }

    private static function matchesLocation(array $item, string $location): bool
    {
        return self::searchHaystack($item, $location, ['location', 'location_search', 'vendor_name', 'title', 'summary', 'description', 'body_html']);
    }

    private static function matchesDate(array $item, string $date): bool
    {
        $startAt = self::carbonFromTimestamp($item['start_timestamp'] ?? null);
        $endAt = self::carbonFromTimestamp($item['end_timestamp'] ?? null);
        if (!$startAt) {
            return false;
        }

        $timezone = self::itemTimezone($item);
        $now = Carbon::now($timezone);

        if (in_array($date, ['past', 'previous'], true)) {
            return self::isPast($item);
        }

        if (in_array($date, ['upcoming', 'future', 'next'], true)) {
            return !self::isPast($item);
        }

        if (in_array($date, ['today', 'tonight'], true)) {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
            return self::rangeOverlaps($startAt, $endAt, $start, $end);
        }

        if (in_array($date, ['tomorrow'], true)) {
            $start = $now->copy()->addDay()->startOfDay();
            $end = $now->copy()->addDay()->endOfDay();
            return self::rangeOverlaps($startAt, $endAt, $start, $end);
        }

        if (in_array($date, ['this_week', 'week'], true)) {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
            return self::rangeOverlaps($startAt, $endAt, $start, $end);
        }

        if (in_array($date, ['this_month', 'month'], true)) {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
            return self::rangeOverlaps($startAt, $endAt, $start, $end);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            $day = Carbon::parse($date, $timezone)->startOfDay();
            return self::rangeOverlaps($startAt, $endAt, $day->copy()->startOfDay(), $day->copy()->endOfDay());
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})\s*(?:\.{2}|to|-)\s*(\d{4}-\d{2}-\d{2})$/', $date, $matches) === 1) {
            try {
                $rangeStart = Carbon::parse($matches[1], $timezone)->startOfDay();
                $rangeEnd = Carbon::parse($matches[2], $timezone)->endOfDay();
                if ($rangeEnd->lt($rangeStart)) {
                    [$rangeStart, $rangeEnd] = [$rangeEnd, $rangeStart];
                }

                return self::rangeOverlaps($startAt, $endAt, $rangeStart, $rangeEnd);
            } catch (\Throwable $e) {
                return true;
            }
        }

        try {
            $parsed = Carbon::parse($date, $timezone);
            return self::rangeOverlaps($startAt, $endAt, $parsed->copy()->startOfDay(), $parsed->copy()->endOfDay());
        } catch (\Throwable $e) {
            return true;
        }
    }

    private static function searchHaystack(array $item, string $needle, array $fields = ['search_blob']): bool
    {
        $needle = trim($needle);
        if ($needle === '') {
            return true;
        }

        $haystack = [];
        foreach ($fields as $field) {
            $value = data_get($item, $field, '');
            if (is_array($value)) {
                $value = implode(' ', array_filter(array_map('strval', $value)));
            }

            $haystack[] = (string) $value;
        }

        $haystack = Str::lower(trim(implode(' ', array_filter($haystack))));
        if ($haystack === '') {
            return false;
        }

        return str_contains($haystack, Str::lower($needle));
    }

    private static function sortDistance(array $item): int
    {
        $start = self::timestampValue($item['start_timestamp'] ?? null);
        if ($start <= 0) {
            return PHP_INT_MAX;
        }

        return abs($start - Carbon::now()->timestamp);
    }

    private static function isPast(array $item): bool
    {
        $end = self::timestampValue($item['end_timestamp'] ?? null);
        if ($end <= 0) {
            $end = self::timestampValue($item['start_timestamp'] ?? null);
        }

        if ($end <= 0) {
            return false;
        }

        return $end < Carbon::now()->timestamp;
    }

    private static function itemTimezone(array $item): string
    {
        $timezone = trim((string) data_get($item, 'timezone', config('app.timezone', 'UTC')));
        return $timezone !== '' ? $timezone : config('app.timezone', 'UTC');
    }

    private static function carbonFromTimestamp(mixed $value): ?Carbon
    {
        $timestamp = self::timestampValue($value);
        if ($timestamp <= 0) {
            return null;
        }

        try {
            return Carbon::createFromTimestamp($timestamp, 'UTC');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function rangeOverlaps(Carbon $itemStart, ?Carbon $itemEnd, Carbon $rangeStart, Carbon $rangeEnd): bool
    {
        $start = $itemStart->copy();
        $end = $itemEnd ? $itemEnd->copy() : $itemStart->copy();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        return $start->lte($rangeEnd) && $end->gte($rangeStart);
    }

    private static function timestampValue(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value)->timestamp;
            } catch (\Throwable $e) {
                return 0;
            }
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->timestamp;
        }

        return 0;
    }

    private static function canonicalSlug(string $sourceType, int $id, string $sourceSlug): string
    {
        $sourceType = trim($sourceType) !== '' ? trim($sourceType) : 'event';
        $slug = self::normalizeSlug($sourceSlug);
        if ($slug === '') {
            $slug = 'event';
        }

        return self::normalizeSlug(sprintf('%s-%d-%s', $sourceType, $id, $slug));
    }

    private static function normalizeSlug(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $path = parse_url($value, PHP_URL_PATH);
        if (is_string($path) && trim($path) !== '') {
            $value = basename(trim($path, '/'));
        }

        return Str::slug($value);
    }

    private static function plainTextExcerpt(string $value, int $limit = 160): string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5);
        $cleaned = trim(preg_replace('/\s+/', ' ', strip_tags($decoded) ?? '') ?: '');

        if ($cleaned === '') {
            return '';
        }

        return Str::limit($cleaned, $limit, '…');
    }

    private static function richTextFromSource(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return ContentFormatter::format($value);
    }

    private static function searchBlob(array $parts): string
    {
        $tokens = [];
        foreach ($parts as $part) {
            if ($part === null) {
                continue;
            }

            if (is_array($part)) {
                $part = implode(' ', array_filter(array_map('strval', $part)));
            }

            $text = trim((string) $part);
            if ($text !== '') {
                $tokens[] = $text;
            }
        }

        return Str::lower(trim(implode(' ', $tokens)));
    }

    private static function normaliseLocations(array $locations): array
    {
        $clean = [];
        foreach ($locations as $location) {
            $label = trim((string) $location);
            if ($label === '') {
                continue;
            }

            if (Str::contains(Str::lower($label), ['online', 'virtual'])) {
                $label = 'Online';
            }

            $clean[] = $label;
        }

        return array_values(array_unique($clean));
    }

    private static function primaryLocationLabel(array $locations): string
    {
        $hasOnline = in_array('Online', $locations, true);
        $physical = array_values(array_filter($locations, fn (string $location): bool => $location !== 'Online'));

        if ($hasOnline && $physical === []) {
            return 'Online';
        }

        if ($hasOnline && $physical !== []) {
            return 'Online • ' . implode(' • ', array_slice($physical, 0, 2));
        }

        if ($physical !== []) {
            return implode(' • ', array_slice($physical, 0, 2));
        }

        return '';
    }

    private static function formatFromLocations(array $locations): ?string
    {
        $hasOnline = in_array('Online', $locations, true);
        $physical = array_values(array_filter($locations, fn (string $location): bool => $location !== 'Online'));

        if ($hasOnline && $physical === []) {
            return 'online';
        }

        if ($hasOnline && $physical !== []) {
            return 'mixed';
        }

        if ($physical !== []) {
            return 'in_person';
        }

        return null;
    }

    private static function eventKind(string $title, string $typeName, string $categoryName, string $summary): string
    {
        $haystack = Str::lower(trim(implode(' ', array_filter([
            $title,
            $typeName,
            $categoryName,
            $summary,
        ]))));

        if ($haystack !== '' && str_contains($haystack, 'workshop')) {
            return 'workshop';
        }

        return 'event';
    }

    private static function slugMatchScore(array $item, string $needle): ?int
    {
        if ($needle === '') {
            return null;
        }

        if (self::normalizeSlug((string) ($item['slug'] ?? '')) === $needle) {
            return 0;
        }

        foreach (['source_slug', 'handle'] as $field) {
            if (self::normalizeSlug((string) ($item[$field] ?? '')) === $needle) {
                return 1;
            }
        }

        if (self::normalizeSlug((string) ($item['title'] ?? '')) === $needle) {
            return 2;
        }

        return null;
    }

    private static function perPage(array $query): int
    {
        $perPage = (int) ($query['per_page'] ?? self::DEFAULT_PER_PAGE);
        if ($perPage <= 0) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        return min(self::MAX_PER_PAGE, max(1, $perPage));
    }

    private static function normaliseQuery(array $query): array
    {
        $filtered = [];
        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
            }

            $filtered[$key] = $value;
        }

        ksort($filtered);

        return $filtered;
    }

    private static function cacheKey(string $suffix): string
    {
        return 'wow:events:' . $suffix;
    }

    private static function rememberSafely(string $key, mixed $ttl, callable $callback): mixed
    {
        try {
            if (Cache::has($key)) {
                return Cache::get($key);
            }
        } catch (\Throwable $e) {
            // Cache backend is unavailable or not writable. Fall back to a live build.
        }

        $value = $callback();
        if ($value === null) {
            return null;
        }

        try {
            Cache::put($key, $value, $ttl);
        } catch (\Throwable $e) {
            // Ignore cache write failures so the request can still succeed.
        }

        return $value;
    }

    private static function moneyValue(mixed $value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        $numeric = (float) $value;
        if ($numeric >= 1000) {
            $numeric /= 100;
        }

        return round($numeric, 2);
    }
}
