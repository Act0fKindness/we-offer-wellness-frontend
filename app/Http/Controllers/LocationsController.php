<?php

namespace App\Http\Controllers;

use App\Models\OfferingV3;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
        $locations = $this->locationsIndex();

        return view('locations.index', [
            'seo' => [
                'title' => 'Locations | We Offer Wellness™',
                'description' => 'Browse wellness experiences by location, including online options and locations near you.',
                'robots' => 'index,follow',
            ],
            'locations' => $locations,
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
        $postcode = trim((string) $request->query('postcode', ''));

        if ($postcode !== '') {
            $request->session()->put('near_me_postcode', $postcode);

            // Later: resolve postcode -> nearest /locations/{slug}
            return redirect()->route('locations.index', ['postcode' => $postcode]);
        }

        return view('near-me.index', [
            'seo' => [
                'title' => 'Near Me | We Offer Wellness™',
                'description' => 'Find wellness experiences near you. Enter your postcode to see what’s available locally.',
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
                'seo_title' => $this->seoTitleForLocation('Online'),
                'seo_description' => 'Browse online experiences you can join from anywhere.',
            ],
            [
                'key' => 'london',
                'slug' => 'london',
                'title' => 'London',
                'seo_title' => $this->seoTitleForLocation('London'),
                'seo_description' => 'Explore wellness experiences and therapies across London.',
            ],
            [
                'key' => 'manchester',
                'slug' => 'manchester',
                'title' => 'Manchester',
                'seo_title' => $this->seoTitleForLocation('Manchester'),
                'seo_description' => 'Find wellness experiences and therapies across Manchester.',
            ],
            [
                'key' => 'birmingham',
                'slug' => 'birmingham',
                'title' => 'Birmingham',
                'seo_title' => $this->seoTitleForLocation('Birmingham'),
                'seo_description' => 'Discover wellness experiences and therapies across Birmingham.',
            ],
            [
                'key' => 'leeds',
                'slug' => 'leeds',
                'title' => 'Leeds',
                'seo_title' => $this->seoTitleForLocation('Leeds'),
                'seo_description' => 'Explore wellness experiences and therapies across Leeds.',
            ],
            [
                'key' => 'bristol',
                'slug' => 'bristol',
                'title' => 'Bristol',
                'seo_title' => $this->seoTitleForLocation('Bristol'),
                'seo_description' => 'Discover wellness experiences and therapies across Bristol.',
            ],
            [
                'key' => 'brighton',
                'slug' => 'brighton',
                'title' => 'Brighton',
                'seo_title' => $this->seoTitleForLocation('Brighton'),
                'seo_description' => 'Find wellness experiences and therapies across Brighton.',
            ],
            [
                'key' => 'liverpool',
                'slug' => 'liverpool',
                'title' => 'Liverpool',
                'seo_title' => $this->seoTitleForLocation('Liverpool'),
                'seo_description' => 'Explore wellness experiences and therapies across Liverpool.',
            ],
            [
                'key' => 'glasgow',
                'slug' => 'glasgow',
                'title' => 'Glasgow',
                'seo_title' => $this->seoTitleForLocation('Glasgow'),
                'seo_description' => 'Discover wellness experiences and therapies across Glasgow.',
            ],
            [
                'key' => 'edinburgh',
                'slug' => 'edinburgh',
                'title' => 'Edinburgh',
                'seo_title' => $this->seoTitleForLocation('Edinburgh'),
                'seo_description' => 'Find wellness experiences and therapies across Edinburgh.',
            ],
            [
                'key' => 'cardiff',
                'slug' => 'cardiff',
                'title' => 'Cardiff',
                'seo_title' => $this->seoTitleForLocation('Cardiff'),
                'seo_description' => 'Explore wellness experiences and therapies across Cardiff.',
            ],
            [
                'key' => 'kent',
                'slug' => 'kent',
                'title' => 'Kent',
                'seo_title' => $this->seoTitleForLocation('Kent'),
                'seo_description' => 'Discover wellness experiences and therapies across Kent.',
            ],
        ];
    }

    private function seoTitleForLocation(string $location): string
    {
        return 'Holistic Health & Wellness Therapies, Classes & Events in ' . trim($location) . ' | We Offer Wellness';
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
}
