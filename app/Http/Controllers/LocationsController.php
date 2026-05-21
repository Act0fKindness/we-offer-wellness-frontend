<?php

namespace App\Http\Controllers;

use App\Models\OfferingV3;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
            return redirect()->route('locations.index', array_merge($request->query(), [
                'place' => $resolved['place'] ?? $query,
                'postcode' => $request->query('postcode', $query),
                'town' => $resolved['town'] ?? null,
                'city' => $resolved['town'] ?? null,
                'county' => $resolved['county'] ?? null,
                'region' => $resolved['region'] ?? null,
                'country' => $resolved['country'] ?? null,
                'lat' => $resolved['lat'] ?? null,
                'lng' => $resolved['lng'] ?? null,
            ]));
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

    public function show(Request $request, string $slug)
    {
        $location = $this->findLocationBySlug($slug);
        abort_if($location === null, 404);

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
        return [
            [
                'key' => 'online',
                'slug' => 'online',
                'title' => 'Online',
                'online' => true,
                'seo_title' => $this->seoTitleForLocation('Online'),
                'seo_description' => 'Browse online experiences you can join from anywhere.',
            ],
            [
                'key' => 'london',
                'slug' => 'london',
                'title' => 'London',
                'lat' => 51.5072,
                'lng' => -0.1276,
                'seo_title' => $this->seoTitleForLocation('London'),
                'seo_description' => 'Explore wellness experiences and therapies across London.',
            ],
            [
                'key' => 'manchester',
                'slug' => 'manchester',
                'title' => 'Manchester',
                'lat' => 53.4808,
                'lng' => -2.2426,
                'seo_title' => $this->seoTitleForLocation('Manchester'),
                'seo_description' => 'Find wellness experiences and therapies across Manchester.',
            ],
            [
                'key' => 'birmingham',
                'slug' => 'birmingham',
                'title' => 'Birmingham',
                'lat' => 52.4862,
                'lng' => -1.8904,
                'seo_title' => $this->seoTitleForLocation('Birmingham'),
                'seo_description' => 'Discover wellness experiences and therapies across Birmingham.',
            ],
            [
                'key' => 'leeds',
                'slug' => 'leeds',
                'title' => 'Leeds',
                'lat' => 53.8008,
                'lng' => -1.5491,
                'seo_title' => $this->seoTitleForLocation('Leeds'),
                'seo_description' => 'Explore wellness experiences and therapies across Leeds.',
            ],
            [
                'key' => 'bristol',
                'slug' => 'bristol',
                'title' => 'Bristol',
                'lat' => 51.4545,
                'lng' => -2.5879,
                'seo_title' => $this->seoTitleForLocation('Bristol'),
                'seo_description' => 'Discover wellness experiences and therapies across Bristol.',
            ],
            [
                'key' => 'brighton',
                'slug' => 'brighton',
                'title' => 'Brighton',
                'lat' => 50.8225,
                'lng' => -0.1372,
                'seo_title' => $this->seoTitleForLocation('Brighton'),
                'seo_description' => 'Find wellness experiences and therapies across Brighton.',
            ],
            [
                'key' => 'liverpool',
                'slug' => 'liverpool',
                'title' => 'Liverpool',
                'lat' => 53.4084,
                'lng' => -2.9916,
                'seo_title' => $this->seoTitleForLocation('Liverpool'),
                'seo_description' => 'Explore wellness experiences and therapies across Liverpool.',
            ],
            [
                'key' => 'glasgow',
                'slug' => 'glasgow',
                'title' => 'Glasgow',
                'lat' => 55.8642,
                'lng' => -4.2518,
                'seo_title' => $this->seoTitleForLocation('Glasgow'),
                'seo_description' => 'Discover wellness experiences and therapies across Glasgow.',
            ],
            [
                'key' => 'edinburgh',
                'slug' => 'edinburgh',
                'title' => 'Edinburgh',
                'lat' => 55.9533,
                'lng' => -3.1883,
                'seo_title' => $this->seoTitleForLocation('Edinburgh'),
                'seo_description' => 'Find wellness experiences and therapies across Edinburgh.',
            ],
            [
                'key' => 'cardiff',
                'slug' => 'cardiff',
                'title' => 'Cardiff',
                'lat' => 51.4816,
                'lng' => -3.1791,
                'seo_title' => $this->seoTitleForLocation('Cardiff'),
                'seo_description' => 'Explore wellness experiences and therapies across Cardiff.',
            ],
            [
                'key' => 'kent',
                'slug' => 'kent',
                'title' => 'Kent',
                'lat' => 51.2745,
                'lng' => 0.5210,
                'seo_title' => $this->seoTitleForLocation('Kent'),
                'seo_description' => 'Discover wellness experiences and therapies across Kent.',
            ],
        ];
    }

    private function seoTitleForLocation(string $location): string
    {
        return 'Holistic Health & Wellness Therapies, Classes & Events in ' . trim($location) . ' | We Offer Wellness';
    }

    private function seoTitleForSearch(string $location): string
    {
        return 'Holistic Health & Wellness Therapies, Classes & Events near ' . trim($location) . ' | We Offer Wellness';
    }

    private function findLocationBySlug(string $slug): ?array
    {
        foreach ($this->locationsIndex() as $location) {
            if (($location['slug'] ?? null) === $slug) {
                return $location;
            }
        }
        return null;
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
        $typeSeg = $this->typeSegment($product);
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
        $product->setAttribute('url', url('/' . $typeSeg . '/' . $product->id . '-' . $slug));

        return $product;
    }

    private function decorateOffering(OfferingV3 $offering): OfferingV3
    {
        $typeSeg = $this->offeringTypeSegment((string) ($offering->type?->name ?? $offering->category?->name ?? 'therapies'));
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
        $offering->setAttribute('url', url('/' . $typeSeg . '/' . $offering->id . '-' . $slug));
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
                'county' => $county,
                'region' => $region,
                'country' => $country,
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
                    'county' => $location['title'] ?? '',
                    'region' => '',
                    'country' => 'United Kingdom',
                    'lat' => $location['lat'] ?? null,
                    'lng' => $location['lng'] ?? null,
                ];
            }
        }

        return null;
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
