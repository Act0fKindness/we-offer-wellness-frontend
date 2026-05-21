<?php

namespace App\Http\Controllers;

use App\Models\OfferingV3;
use App\Models\Product;
use App\Models\VendorLocation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LocationsController extends Controller
{
    private const VENDOR_LOCATION_COLUMNS = [
        'label',
        'line1',
        'line2',
        'city',
        'county',
        'postcode',
        'formatted_address',
        'country',
    ];

    public function index(Request $request)
    {
        $query = trim((string) $request->query('place', $request->query('postcode', $request->query('q', ''))));
        $resolved = $query !== '' ? $this->resolveSearchOrigin($query) : null;

        if (
            $resolved !== null &&
            !$request->hasAny(['town', 'city', 'county', 'country', 'region']) &&
            (
                !empty($resolved['town']) ||
                !empty($resolved['county']) ||
                !empty($resolved['country']) ||
                !empty($resolved['region'])
            )
        ) {
            return redirect()->to($this->hierarchyPathFromResolved($resolved));
        }

        $locations = $resolved ? $this->rankLocationsByDistance($resolved) : $this->locationsIndex();
        $nearbyPhysical = collect($locations)
            ->filter(fn (array $location): bool => !($location['online'] ?? false) && isset($location['distance_miles']))
            ->values();
        $nearestDistance = (float) ($nearbyPhysical->first()['distance_miles'] ?? 0);
        $onlinePreferred = $resolved !== null && ($nearbyPhysical->isEmpty() || $nearestDistance > 40);

        return view('locations.index', [
            'seo' => [
                'title' => $resolved
                    ? $this->seoTitleForSearch($resolved['label'] ?? $query)
                    : 'Locations | We Offer Wellness®',
                'description' => $resolved
                    ? 'Browse wellness locations ranked by distance from ' . ($resolved['label'] ?? $query) . ', plus online support if nearby options are limited.'
                    : 'Browse wellness experiences by location, including online options and locations near you.',
                'robots' => $resolved ? 'noindex,follow' : 'index,follow',
            ],
            'locations' => $locations,
            'locationSearch' => $resolved,
            'locationQuery' => $query,
            'onlinePreferred' => $onlinePreferred,
        ]);
    }

    public function hierarchy(Request $request, string $country, ?string $county = null, ?string $town = null)
    {
        $countrySlug = Str::slug($country);
        abort_unless($countrySlug === 'united-kingdom', 404);

        $countySlug = $county !== null ? Str::slug($county) : null;
        $townSlug = $town !== null ? Str::slug($town) : null;

        $canonicalPath = $this->canonicalLocationPath($countrySlug, $countySlug, $townSlug);
        $requestedPath = $this->hierarchyPath($countrySlug, $countySlug, $townSlug);

        if ($canonicalPath !== $requestedPath) {
            $target = url($canonicalPath);
            if ($request->getQueryString()) {
                $target .= '?' . $request->getQueryString();
            }

            return redirect()->to($target, 301);
        }

        $countryLabel = 'United Kingdom';
        $countyLabel = $countySlug ? Str::of(str_replace('-', ' ', $countySlug))->headline()->toString() : null;
        $townLabel = $townSlug ? Str::of(str_replace('-', ' ', $townSlug))->headline()->toString() : null;
        $placeLabel = $townLabel ?: $countyLabel ?: $countryLabel;
        $query = trim(implode(', ', array_filter([$placeLabel, $countryLabel])));
        $resolved = $this->resolveSearchOrigin($query) ?? [
            'label' => $query,
            'place' => $placeLabel,
            'town' => $townLabel,
            'city' => $townLabel,
            'county' => $countyLabel,
            'region' => $countyLabel,
            'country' => $countryLabel,
            'lat' => null,
            'lng' => null,
        ];

        $resolved['country_slug'] = $countrySlug;
        $resolved['county_slug'] = $countySlug;
        $resolved['town_slug'] = $townSlug;
        $resolved['path'] = $canonicalPath;
        $resolved['label'] = $query;

        $locations = $this->rankLocationsByDistance($resolved);
        $nearbyPhysical = collect($locations)
            ->filter(fn (array $location): bool => !($location['online'] ?? false) && isset($location['distance_miles']))
            ->values();
        $nearestDistance = (float) ($nearbyPhysical->first()['distance_miles'] ?? 0);
        $onlinePreferred = ($nearbyPhysical->isEmpty() || $nearestDistance > 40);

        return view('locations.index', [
            'seo' => [
                'title' => $this->seoTitleForHierarchy($countryLabel, $countyLabel, $placeLabel),
                'description' => $this->seoDescriptionForHierarchy($countryLabel, $countyLabel, $placeLabel),
                'robots' => 'index,follow',
                'canonical' => url($resolved['path']),
            ],
            'locations' => $locations,
            'locationSearch' => $resolved,
            'locationQuery' => trim(implode(' ', array_filter([$placeLabel, $countryLabel]))),
            'onlinePreferred' => $onlinePreferred,
        ]);
    }

    public function locationPages(): array
    {
        return $this->locationsIndex();
    }

    public function show(Request $request, string $slug)
    {
        $location = $this->findLocationBySlug($slug);
        abort_if($location === null, 404);

        if (!empty($location['path'])) {
            $target = url($location['path']);
            if ($request->getQueryString()) {
                $target .= '?' . $request->getQueryString();
            }

            return redirect()->to($target, 301);
        }

        $filters = [
            'location' => $location['key'],
            'format'   => (string) $request->query('format', ''),
            'sort'     => (string) $request->query('sort', ''),
            'page'     => max(1, (int) $request->query('page', 1)),
            'per_page' => min(48, max(8, (int) $request->query('per_page', 24))),
        ];

        $results = $this->fetchOfferings($filters);
        $items = collect($results['items'] ?? []);
        $resultCount = (int) ($results['meta']['total'] ?? $items->count());

        $hasFacets = (bool) (
            $filters['format'] ||
            $filters['sort'] ||
            $request->has('page') ||
            $request->has('per_page')
        );

        return view('locations.show', [
            'seo' => [
                'title' => $this->seoTitleForLocation((string) ($location['title'] ?? 'Location')),
                'description' => $location['seo_description'] ?? ('Discover holistic health and wellness therapies, classes and events in ' . $location['title'] . '.'),
                'robots' => $hasFacets ? 'noindex,follow' : 'index,follow',
                'canonical' => url('/locations/' . $slug),
            ],
            'location' => $location,
            'filters' => $filters,
            'results' => $results,
            'products' => new LengthAwarePaginator(
                $items,
                $resultCount,
                max(1, (int) ($results['meta']['per_page'] ?? count($items) ?: 24)),
                max(1, (int) ($results['meta']['current_page'] ?? 1)),
                ['path' => url()->current(), 'query' => $request->query()]
            ),
            'resultCount' => $resultCount,
        ]);
    }

    public function nearMe(Request $request)
    {
        // UX page (user-specific): enter postcode, then you can later resolve it to a nearest location.
        $postcode = trim((string) $request->query('place', $request->query('postcode', '')));

        if ($postcode !== '') {
            $request->session()->put('near_me_postcode', $postcode);

            // Later: resolve postcode -> nearest /locations/{slug}
            return redirect()->route('locations.index', ['place' => $postcode, 'postcode' => $postcode]);
        }

        return view('near-me.index', [
            'seo' => [
                'title' => 'Near Me | We Offer Wellness™',
                'description' => 'Find wellness experiences near you. Enter your location to see what’s available locally.',
                'robots' => 'noindex,follow',
                'canonical' => url('/near-me'),
            ],
        ]);
    }

    private function locationsIndex(): array
    {
        return Cache::remember('wow.locations.catalog', now()->addHour(), function (): array {
            $pages = [];

            $pages[] = [
                'key' => 'online',
                'slug' => 'online',
                'title' => 'Online',
                'online' => true,
                'path' => '/online',
                'seo_title' => $this->seoTitleForLocation('Online'),
                'seo_description' => 'Browse online experiences you can join from anywhere.',
            ];

            $rows = VendorLocation::query()
                ->select(['city', 'county_region', 'country', 'lat', 'lng', 'label', 'formatted_address'])
                ->where(function ($query): void {
                    $query->whereNotNull('city')
                        ->orWhereNotNull('county_region')
                        ->orWhereNotNull('country');
                })
                ->orderByRaw("LOWER(COALESCE(country, ''))")
                ->orderByRaw("LOWER(COALESCE(county_region, ''))")
                ->orderByRaw("LOWER(COALESCE(city, ''))")
                ->get();

            $seen = [];

            foreach ($rows as $row) {
                $countryLabel = trim((string) ($row->country ?? ''));
                $countyLabel = trim((string) ($row->county_region ?? ''));
                $cityLabel = trim((string) ($row->city ?? ''));

                $countrySlug = $this->normalizeCountrySlug($countryLabel);
                $countySlug = $this->normalizeCountySlug($countyLabel);
                $citySlug = Str::slug($cityLabel);

                $path = $this->canonicalLocationPath($countrySlug, $countySlug, $citySlug);
                if (isset($seen[$path])) {
                    continue;
                }

                $title = $cityLabel !== ''
                    ? $cityLabel
                    : ($countyLabel !== '' ? Str::of($countyLabel)->headline()->toString() : Str::of($countryLabel ?: 'United Kingdom')->headline()->toString());

                $placeSummary = collect(array_filter([
                    $cityLabel !== '' ? $cityLabel : null,
                    $countyLabel !== '' ? Str::of($countyLabel)->headline()->toString() : null,
                    $this->labelForCountrySlug($countrySlug),
                ]))->implode(', ');

                $pages[] = [
                    'key' => trim($path, '/'),
                    'slug' => trim(str_replace('/locations/', '', $path), '/'),
                    'title' => $title,
                    'country_slug' => $countrySlug,
                    'county_slug' => $countySlug ?: null,
                    'town_slug' => $citySlug ?: null,
                    'path' => $path,
                    'lat' => isset($row->lat) ? (float) $row->lat : null,
                    'lng' => isset($row->lng) ? (float) $row->lng : null,
                    'seo_title' => $this->seoTitleForLocation($title),
                    'seo_description' => 'Discover wellness experiences and therapies across ' . $placeSummary . '.',
                ];

                $seen[$path] = true;
            }

            usort($pages, function (array $left, array $right): int {
                if (!empty($left['online']) && empty($right['online'])) {
                    return -1;
                }

                if (empty($left['online']) && !empty($right['online'])) {
                    return 1;
                }

                return strcasecmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
            });

            return $pages;
        });
    }

    private function seoTitleForLocation(string $location): string
    {
        return 'Holistic Health & Wellness Therapies, Classes & Events in ' . trim($location) . ' | We Offer Wellness';
    }

    private function seoTitleForSearch(string $location): string
    {
        return 'Holistic Health & Wellness Therapies, Classes & Events near ' . trim($location) . ' | We Offer Wellness';
    }

    private function seoTitleForHierarchy(string $country, ?string $county = null, ?string $town = null): string
    {
        $parts = array_values(array_unique(array_filter([$town, $county, $country])));
        $location = implode(', ', $parts);

        return 'Holistic Health & Wellness Therapies, Classes & Events in ' . $location . ' | We Offer Wellness';
    }

    private function seoDescriptionForHierarchy(string $country, ?string $county = null, ?string $town = null): string
    {
        $parts = array_values(array_unique(array_filter([$town, $county, $country])));
        $location = implode(', ', $parts);

        return 'Discover holistic health and wellness therapies, classes and events in ' . $location . ', with online options when nearby choices are limited.';
    }

    private function findLocationBySlug(string $slug): ?array
    {
        $needle = trim(Str::of($slug)->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->toString());

        foreach ($this->locationsIndex() as $location) {
            $slugValue = trim(Str::of((string) ($location['slug'] ?? ''))->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->toString());
            $titleValue = trim(Str::of((string) ($location['title'] ?? ''))->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->toString());
            if ($needle !== '' && ($needle === $slugValue || $needle === $titleValue)) {
                return $location;
            }
        }
        return null;
    }

    private function hierarchyPath(string $countrySlug, ?string $countySlug = null, ?string $townSlug = null): string
    {
        $segments = ['/locations', trim($countrySlug, '/')];
        $countySlug = $countySlug !== null ? trim($countySlug, '/') : null;
        $townSlug = $townSlug !== null ? trim($townSlug, '/') : null;

        $genericCounty = $countySlug !== null && $this->isGenericCountySlug($countySlug);
        $sameAsTown = $countySlug !== null && $townSlug !== null && $countySlug === $townSlug;

        if ($countySlug !== null && !$genericCounty && !$sameAsTown) {
            $segments[] = $countySlug;
        }

        if ($townSlug !== null && $townSlug !== '') {
            if ($townSlug !== $countrySlug) {
                if ($townSlug !== $countySlug || $genericCounty || $sameAsTown) {
                    $segments[] = $townSlug;
                }
            }
        }

        return implode('/', $segments);
    }

    private function hierarchyPathFromResolved(array $resolved): string
    {
        $countrySlug = $this->normalizeCountrySlug((string) ($resolved['country'] ?? ($resolved['country_slug'] ?? 'united-kingdom')));
        $countySlug = $this->normalizeCountySlug((string) ($resolved['county_slug'] ?? ($resolved['county'] ?? $resolved['region'] ?? '')));
        $townSlug = Str::slug((string) ($resolved['town_slug'] ?? ($resolved['town'] ?? $resolved['place'] ?? '')));

        return $this->canonicalLocationPath($countrySlug, $countySlug, $townSlug);
    }

    private function fetchOfferings(array $query): array
    {
        $location = trim((string) ($query['location'] ?? ''));
        $format = strtolower(trim((string) ($query['format'] ?? '')));
        $sort = strtolower(trim((string) ($query['sort'] ?? 'popular')));
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = min(48, max(8, (int) ($query['per_page'] ?? 24)));

        $items = $this->buildLocationItems($location, $format, $sort);
        $total = $items->count();
        $lastPage = max(1, (int) ceil($total / max(1, $perPage)));
        $page = min($page, $lastPage);
        $pageItems = $items->forPage($page, $perPage)->values();

        return [
            'items' => $pageItems->all(),
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ];
    }

    private function buildLocationItems(string $location, string $format, string $sort): Collection
    {
        $city = trim($location);
        $cityLike = $city !== '' ? '%'.$city.'%' : null;

        $products = Product::query()
            ->with(['media', 'options.values', 'category', 'vendor.locations', 'vendor.tiers'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->withMax('variants', 'price')
            ->where(function ($query) {
                $query->whereHas('status', function ($status) {
                    $status->whereIn('status', ['live', 'approved']);
                });
            });

        $this->applyLocationFilter($products, $cityLike);
        $this->applyFormatFilter($products, $format);

        $offerings = OfferingV3::query()
            ->with(['category', 'type', 'vendor.locations', 'vendor.tiers', 'media', 'coverMedia'])
            ->whereIn('status', ['live', 'approved']);

        $this->applyOfferingLocationFilter($offerings, $cityLike);
        $this->applyOfferingFormatFilter($offerings, $format);

        $items = $products->get()->map(fn (Product $product) => $this->decorateProduct($product));
        $items = $items->concat($offerings->get()->map(fn (OfferingV3 $offering) => $this->decorateOffering($offering)));

        $items = $items->sort(function ($left, $right) use ($sort) {
            $leftScore = $this->locationSortScore($left, $sort);
            $rightScore = $this->locationSortScore($right, $sort);

            if ($leftScore === $rightScore) {
                return strcasecmp((string) ($left->title ?? ''), (string) ($right->title ?? ''));
            }

            return $rightScore <=> $leftScore;
        })->values();

        return $items;
    }

    private function applyLocationFilter($query, ?string $cityLike): void
    {
        if ($cityLike === null) {
            return;
        }

        $query->where(function ($q) use ($cityLike) {
            $q->whereHas('options', function ($oq) use ($cityLike) {
                $oq->where('meta_name', 'locations')
                    ->whereHas('values', function ($vq) use ($cityLike) {
                        $vq->where('value', 'like', $cityLike);
                    });
            })->orWhereHas('vendor.locations', function ($vq) use ($cityLike) {
                $vq->where(function ($locationQuery) use ($cityLike) {
                    $first = true;
                    foreach (self::VENDOR_LOCATION_COLUMNS as $column) {
                        $condition = "LOWER(COALESCE({$column}, '')) LIKE ?";
                        if ($first) {
                            $locationQuery->whereRaw($condition, [strtolower($cityLike)]);
                            $first = false;
                        } else {
                            $locationQuery->orWhereRaw($condition, [strtolower($cityLike)]);
                        }
                    }
                });
            });
        });
    }

    private function applyFormatFilter($query, string $format): void
    {
        if ($format === 'online') {
            $query->whereHas('options', function ($oq) {
                $oq->where('meta_name', 'locations')
                    ->whereHas('values', function ($vq) {
                        $vq->whereRaw("LOWER(TRIM(COALESCE(value,''))) = 'online'");
                    });
            });
        } elseif ($format === 'in_person') {
            $query->whereHas('options', function ($oq) {
                $oq->where('meta_name', 'locations')
                    ->whereHas('values', function ($vq) {
                        $vq->whereRaw("LOWER(TRIM(COALESCE(value,''))) <> 'online'")
                            ->whereRaw("TRIM(COALESCE(value,'')) <> ''");
                    });
            });
        }
    }

    private function applyOfferingLocationFilter($query, ?string $cityLike): void
    {
        if ($cityLike === null) {
            return;
        }

        $query->where(function ($q) use ($cityLike) {
            $q->whereHas('vendor.locations', function ($vq) use ($cityLike) {
                $vq->where(function ($locationQuery) use ($cityLike) {
                    $first = true;
                    foreach (self::VENDOR_LOCATION_COLUMNS as $column) {
                        $condition = "LOWER(COALESCE({$column}, '')) LIKE ?";
                        if ($first) {
                            $locationQuery->whereRaw($condition, [strtolower($cityLike)]);
                            $first = false;
                        } else {
                            $locationQuery->orWhereRaw($condition, [strtolower($cityLike)]);
                        }
                    }
                });
            });
        });
    }

    private function applyOfferingFormatFilter($query, string $format): void
    {
        if ($format === 'online') {
            $query->where(function ($q) {
                $q->whereRaw("LOWER(COALESCE(summary,'')) like '%online%'")
                    ->orWhereHas('vendor.locations', function ($vq) {
                        $vq->whereRaw("LOWER(COALESCE(location,'')) = 'online'");
                    });
            });
        } elseif ($format === 'in_person') {
            $query->where(function ($q) {
                $q->whereHas('vendor.locations', function ($vq) {
                    $vq->whereRaw("LOWER(COALESCE(location,'')) <> 'online'")
                        ->whereRaw("COALESCE(location,'') <> ''");
                });
            });
        }
    }

    private function decorateProduct(Product $product): Product
    {
        $locations = method_exists($product, 'getLocations') ? $product->getLocations() : [];
        $isOnline = in_array('Online', $locations, true);
        $physical = array_values(array_filter($locations, fn ($l) => $l !== 'Online'));
        $meta = $product->meta_json ?? [];
        $slug = Str::slug($product->title ?: (string) $product->id);

        $product->setAttribute('type', $product->product_type ?: 'experience');
        $product->setAttribute('category', $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null);
        $product->setAttribute('mode', $isOnline && count($physical) === 0 ? 'Online' : (count($physical) ? 'In-person' : null));
        $product->setAttribute('location', $physical[0] ?? ($isOnline ? 'Online' : null));
        $product->setAttribute('locations', $locations);
        $product->setAttribute('price', $product->price ?? null);
        $product->setAttribute('compare_at_price', $meta['compare_at_price'] ?? null);
        $product->setAttribute('currency', $meta['currency'] ?? 'GBP');
        $product->setAttribute('rating', round((float) ($product->reviews_avg_rating ?? 0), 1) ?: null);
        $product->setAttribute('review_count', (int) ($product->reviews_count ?? 0));
        $product->setAttribute('image', method_exists($product, 'getFirstImageUrl') ? $product->getFirstImageUrl() : null);
        $product->setAttribute('tags', $product->tags_list ? array_map('trim', explode(',', $product->tags_list)) : []);
        $product->setAttribute('url', url('/offerings/' . $product->id . '-' . $slug));

        return $product;
    }

    private function decorateOffering(OfferingV3 $offering): OfferingV3
    {
        $slug = Str::slug($offering->title ?: (string) $offering->id);

        $offering->setAttribute('product_type', (string) ($offering->type?->name ?? $offering->category?->name ?? 'experience'));
        $offering->setAttribute('tags_list', trim(implode(',', array_filter([
            (string) ($offering->type?->name ?? ''),
            (string) ($offering->category?->name ?? ''),
            (string) ($offering->summary ?? ''),
        ]))));
        $offering->setAttribute('vendor_name', $offering->vendor?->vendor_name ?? null);
        $offering->setAttribute('variants_min_price', $offering->price);
        $offering->setAttribute('reviews_avg_rating', null);
        $offering->setAttribute('reviews_count', 0);
        $offering->setAttribute('url', url('/offerings/' . $offering->id . '-' . $slug));
        $offering->setAttribute('image', $offering->getFirstImageUrl());
        $offering->setAttribute('locations', $offering->getLocations());
        $offering->setAttribute('mode', $this->offeringMode($offering));
        $offering->setAttribute('rating', null);
        $offering->setAttribute('review_count', 0);

        return $offering;
    }

    private function typeSegment(Product $product): string
    {
        $type = strtolower((string) ($product->product_type ?? ''));
        if (str_contains($type, 'workshop')) {
            return 'workshops';
        }
        if (str_contains($type, 'event')) {
            return 'events';
        }
        if (str_contains($type, 'class')) {
            return 'classes';
        }
        if (str_contains($type, 'retreat')) {
            return 'retreats';
        }
        if (str_contains($type, 'gift')) {
            return 'gifts';
        }

        return 'therapies';
    }

    private function offeringTypeSegment(string $type): string
    {
        $type = strtolower($type);
        if (str_contains($type, 'workshop')) {
            return 'workshops';
        }
        if (str_contains($type, 'event')) {
            return 'events';
        }
        if (str_contains($type, 'class')) {
            return 'classes';
        }
        if (str_contains($type, 'retreat')) {
            return 'retreats';
        }
        if (str_contains($type, 'gift')) {
            return 'gifts';
        }

        return 'therapies';
    }

    private function offeringMode(OfferingV3 $offering): ?string
    {
        $locations = $offering->getLocations();
        $isOnline = in_array('Online', $locations, true);
        $physical = array_values(array_filter($locations, fn ($location) => $location !== 'Online'));

        if ($isOnline && count($physical) === 0) {
            return 'Online';
        }

        if (count($physical) > 0) {
            return 'In-person';
        }

        return null;
    }

    private function locationSortScore($item, string $sort): float
    {
        $price = (float) ($item->price ?? $item->variants_min_price ?? 0);
        $rating = (float) ($item->rating ?? $item->reviews_avg_rating ?? 0);
        $reviews = (float) ($item->review_count ?? $item->reviews_count ?? 0);

        return match ($sort) {
            'price_asc' => -1 * $price,
            'price_desc' => $price,
            'rating_desc' => ($rating * 10) + $reviews,
            default => ($rating * log(1 + max(0, $reviews + 1))),
        };
    }

    private function rankLocationsByDistance(array $resolved): array
    {
        $originLat = isset($resolved['lat']) ? (float) $resolved['lat'] : null;
        $originLng = isset($resolved['lng']) ? (float) $resolved['lng'] : null;
        $locations = $this->locationsIndex();

        if ($originLat === null || $originLng === null) {
            return $locations;
        }

        $physical = [];
        $online = [];

        foreach ($locations as $location) {
            if (($location['online'] ?? false) === true) {
                $location['distance_miles'] = null;
                $location['distance_label'] = 'Available anywhere';
                $online[] = $location;
                continue;
            }

            $lat = isset($location['lat']) ? (float) $location['lat'] : null;
            $lng = isset($location['lng']) ? (float) $location['lng'] : null;
            if ($lat === null || $lng === null) {
                $location['distance_miles'] = null;
                $location['distance_label'] = null;
                $physical[] = $location;
                continue;
            }

            $distance = $this->distanceMiles($originLat, $originLng, $lat, $lng);
            $location['distance_miles'] = $distance;
            $location['distance_label'] = number_format($distance, $distance < 10 ? 1 : 0) . ' miles away';
            $physical[] = $location;
        }

        usort($physical, function (array $left, array $right): int {
            $ld = (float) ($left['distance_miles'] ?? PHP_FLOAT_MAX);
            $rd = (float) ($right['distance_miles'] ?? PHP_FLOAT_MAX);
            if ($ld === $rd) {
                return strcasecmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
            }

            return $ld <=> $rd;
        });

        return array_values(array_merge($online, $physical));
    }

    private function distanceMiles(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 3958.7613;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function resolveSearchOrigin(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $match = $this->matchKnownLocation($query);
        if ($match !== null) {
            return $match;
        }

        $token = trim((string) config('services.mapbox.token'));
        if ($token === '') {
            return ['label' => $query];
        }

        try {
            $url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' . rawurlencode($query) . '.json';
            $response = Http::timeout(8)->get($url, [
                'access_token' => $token,
                'autocomplete' => 'true',
                'limit' => 1,
                'types' => 'place,locality,region,postcode,country,district',
            ]);

            if (!$response->ok()) {
                return ['label' => $query];
            }

            $feature = $response->json('features.0');
            if (!is_array($feature)) {
                return ['label' => $query];
            }

            $context = collect((array) ($feature['context'] ?? []));
            $place = (string) ($feature['text'] ?? $feature['place_name'] ?? $query);
            $region = (string) $this->contextText($context, 'region');
            $country = (string) $this->contextText($context, 'country');
            $locality = (string) $this->contextText($context, 'place');
            $district = (string) $this->contextText($context, 'district');
            $county = $district !== '' ? $district : ($region !== '' ? $region : '');
            $coords = $feature['center'] ?? ($feature['geometry']['coordinates'] ?? null);
            $lng = is_array($coords) && isset($coords[0]) ? (float) $coords[0] : null;
            $lat = is_array($coords) && isset($coords[1]) ? (float) $coords[1] : null;

            return [
                'label' => $feature['place_name'] ?? $query,
                'place' => $place ?: $query,
                'town' => $locality !== '' ? $locality : $place,
                'town_slug' => Str::slug($locality !== '' ? $locality : $place),
                'county' => $county,
                'county_slug' => Str::slug($county),
                'region' => $region,
                'region_slug' => Str::slug($region),
                'country' => $country,
                'country_slug' => $this->normalizeCountrySlug($country),
                'lat' => $lat,
                'lng' => $lng,
                'raw' => $feature,
            ];
        } catch (\Throwable $e) {
            return ['label' => $query];
        }
    }

    private function matchKnownLocation(string $query): ?array
    {
        $needle = Str::of($query)->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->toString();

        foreach ($this->locationsIndex() as $location) {
            $title = Str::of((string) ($location['title'] ?? ''))->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->toString();
            $slug = Str::of((string) ($location['slug'] ?? ''))->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->toString();
            if ($needle !== '' && ($needle === $title || $needle === $slug)) {
                return [
                    'label' => $location['title'] ?? $query,
                    'place' => $location['title'] ?? $query,
                    'town' => $location['title'] ?? $query,
                    'town_slug' => Str::slug($location['title'] ?? $query),
                    'county' => $location['title'] ?? '',
                    'county_slug' => Str::slug($location['title'] ?? $query),
                    'region_slug' => '',
                    'region' => '',
                    'country' => 'United Kingdom',
                    'country_slug' => 'united-kingdom',
                    'lat' => $location['lat'] ?? null,
                    'lng' => $location['lng'] ?? null,
                ];
            }
        }

        return null;
    }

    private function canonicalLocationPath(string $countrySlug, ?string $countySlug = null, ?string $townSlug = null): string
    {
        $countrySlug = $this->normalizeCountrySlug($countrySlug);
        $countySlug = $countySlug !== null ? trim((string) $countySlug, '/') : null;
        $townSlug = $townSlug !== null ? trim((string) $townSlug, '/') : null;

        if ($countySlug !== null && $this->isGenericCountySlug($countySlug)) {
            $countySlug = null;
        }

        if ($townSlug !== null && $townSlug !== '' && $townSlug === $countrySlug) {
            $townSlug = null;
        }

        if ($townSlug !== null && $countySlug !== null && $townSlug === $countySlug) {
            $countySlug = null;
        }

        if ($townSlug !== null && $townSlug === 'london') {
            $countySlug = null;
        }

        $segments = ['/locations', $countrySlug];

        if ($countySlug !== null && $countySlug !== '') {
            $segments[] = $countySlug;
        }

        if ($townSlug !== null && $townSlug !== '') {
            $segments[] = $townSlug;
        }

        return implode('/', $segments);
    }

    private function normalizeCountrySlug(string $country): string
    {
        $slug = Str::slug($country);

        if ($slug === '' || in_array($slug, ['uk', 'u-k', 'gb', 'great-britain', 'united-kingdom', 'england', 'scotland', 'wales', 'northern-ireland'], true)) {
            return 'united-kingdom';
        }

        return $slug;
    }

    private function normalizeCountySlug(string $county): ?string
    {
        $slug = Str::slug($county);

        if ($slug === '' || $this->isGenericCountySlug($slug)) {
            return null;
        }

        return $slug;
    }

    private function isGenericCountySlug(string $slug): bool
    {
        return in_array($slug, ['england', 'scotland', 'wales', 'northern-ireland', 'united-kingdom', 'uk', 'u-k', 'gb', 'great-britain'], true);
    }

    private function labelForCountrySlug(string $countrySlug): string
    {
        return match ($countrySlug) {
            'united-kingdom' => 'United Kingdom',
            default => Str::of($countrySlug)->headline()->toString(),
        };
    }

    private function contextText(Collection $context, string $prefix): ?string
    {
        $item = $context->first(function ($entry) use ($prefix) {
            return str_starts_with((string) ($entry['id'] ?? ''), $prefix . '.');
        });

        if (!is_array($item)) {
            return null;
        }

        $value = trim((string) ($item['text'] ?? ''));

        return $value !== '' ? $value : null;
    }
}
