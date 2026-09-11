<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfferingV3;
use App\Models\Product;
use App\Support\EventListing;
use App\Support\ProductRanking;
use App\Support\ProductSearchFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HomeRailsController extends Controller
{
    public function index(Request $request)
    {
        $section = Str::lower(trim((string) $request->input('section', '')));

        if ($section === 'latest') {
            return response($this->renderCards($this->latestCatalogue(), 'partials.product_card_v4_1', true))
                ->header('Content-Type', 'text/html; charset=UTF-8');
        }

        if ($section === 'gifts') {
            $limit = max(1, min((int) $request->integer('limit', 12), 24));
            $page = max(1, (int) $request->integer('page', 1));
            $pageData = $this->giftsUnder50Page($page, $limit);

            return response($this->renderCards($pageData['items'], 'partials.product_card_v4_1', true))
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('X-Page', (string) $page)
                ->header('X-Page-Size', (string) $limit)
                ->header('X-Has-More', $pageData['has_more'] ? '1' : '0')
                ->header('X-Total-Count', (string) $pageData['total']);
        }

        if ($section === 'comfort') {
            $limit = max(1, min((int) $request->integer('limit', 12), 24));
            $priceMax = (float) $request->input('price_max', 50);
            $groupType = Str::lower(trim((string) $request->input('group_type', 'solo')));
            $mode = Str::lower(trim((string) $request->input('mode', 'online')));

            return response($this->renderCards($this->comfortRail($priceMax, $groupType, $mode)->take($limit)))
                ->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return response('', 404);
    }

    private function renderCards(Collection $items, string $cardView = 'partials.product_card_v4_1', bool $forceNewCard = false): string
    {
        $html = $items
            ->reject(fn ($item) => EventListing::isPast($item))
            ->filter()
            ->map(function ($item) use ($cardView, $forceNewCard): string {
                if (data_get($item, 'kind') === 'physical_product' || data_get($item, 'source_type') === 'physical_product') {
                    return view('partials.store_product_card', ['product' => $item])->render();
                }

                return view($cardView, ['product' => $item, 'preferredLocation' => null, 'forceNewCard' => $forceNewCard])->render();
            })
            ->implode('');

        if ($html !== '') {
            return $html;
        }

        return '';
    }

    private function stampSourceVersion(Collection $items, string $sourceVersion): Collection
    {
        return $items->map(function ($item) use ($sourceVersion) {
            if (is_object($item)) {
                $item->source_version = $sourceVersion;
            }

            return $item;
        })->values();
    }

    private function stampVendorReviewSummary(Collection $items): Collection
    {
        return $items->map(function ($item) {
            if (! is_object($item)) {
                return $item;
            }

            $summary = data_get($item, 'vendor.review_summary');
            if (is_array($summary)) {
                $item->vendor_review_count = (int) ($summary['count'] ?? 0);
                if (array_key_exists('rating', $summary) && is_numeric($summary['rating'])) {
                    $item->vendor_review_rating = round((float) $summary['rating'], 1);
                }
            }

            return $item;
        })->values();
    }

    private function giftsUnder50(): Collection
    {
        $base = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->with(['media', 'options.values', 'category', 'vendor.tiers', 'vendor.user.settings'])
            ->whereHas('status', function ($qs): void {
                $qs->where('status', 'live');
            });

        $giftProducts = (clone $base)
            ->where(function ($q): void {
                $q->whereRaw("LOWER(COALESCE(tags_list,'')) like '%gift%'")
                    ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%voucher%'")
                    ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%card%'")
                    ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%present%'")
                    ->orWhereRaw("LOWER(COALESCE(product_type,'')) like '%gift%'");
            })
            ->where(function ($q): void {
                $q->where(function ($inner): void {
                    $inner->where('price', '<=', 50)->orWhere('price', '<=', 50 * 100);
                })
                    ->orWhere(function ($inner): void {
                        $inner->where('price', '<=', 50 * 100);
                    })
                    ->orWhereHas('variants', function ($qv): void {
                        $qv->where(function ($qq): void {
                            $qq->where('price', '<=', 50)->orWhere('price', '<=', 50 * 100);
                        })
                            ->orWhere(function ($qq): void {
                                $qq->where('price', '<=', 50 * 100);
                            });
                    });
            })
            ->get()
            ->reject(fn ($product) => EventListing::isPast($product))
            ->filter(function ($product): bool {
                $min = $product->variants_min_price ?? $product->price;
                if (! is_numeric($min)) {
                    return false;
                }

                $min = (float) $min;
                if ($min >= 1000) {
                    $min = $min / 100;
                }

                return $min <= 50.0;
            })
            ->values();

        $giftProducts = $this->stampVendorReviewSummary($this->stampSourceVersion($giftProducts, 'v1-v2'));
        $giftOfferings = $this->stampVendorReviewSummary($this->stampSourceVersion($this->giftOfferings(), 'v3'));
        $giftsUnder50 = ProductRanking::sortCollection($giftProducts->concat($giftOfferings), 'review_count_desc')
            ->values();

        if ($giftsUnder50->isEmpty()) {
            $giftFallbackProducts = (clone $base)
                ->where(function ($q): void {
                    $q->where(function ($inner): void {
                        $inner->where('price', '<=', 50)->orWhere('price', '<=', 50 * 100);
                    })
                        ->orWhere(function ($inner): void {
                            $inner->where('price', '<=', 50 * 100);
                        })
                        ->orWhereHas('variants', function ($qv): void {
                            $qv->where(function ($qq): void {
                                $qq->where('price', '<=', 50)->orWhere('price', '<=', 50 * 100);
                            })
                                ->orWhere(function ($qq): void {
                                    $qq->where('price', '<=', 50 * 100);
                                });
                        });
                })
                ->get()
                ->reject(fn ($product) => EventListing::isPast($product))
                ->filter(function ($product): bool {
                    $min = $product->variants_min_price ?? $product->price;
                    if (! is_numeric($min)) {
                        return false;
                    }

                    $min = (float) $min;
                    if ($min >= 1000) {
                        $min = $min / 100;
                    }

                    return $min <= 50.0;
                })
                ->values();

            $giftFallbackOfferings = OfferingV3::query()
                ->with(['category', 'type', 'vendor.tiers', 'vendor.user.settings', 'media', 'coverMedia'])
                ->where('status', 'live')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->get()
                ->reject(fn ($offering) => EventListing::isPast($offering))
                ->filter(function ($offering): bool {
                    $min = ProductRanking::priceValue($offering);
                    return $min !== null && $min <= 50.0;
                })
                ->values();

            $giftFallbackProducts = $this->stampVendorReviewSummary($this->stampSourceVersion($giftFallbackProducts, 'v1-v2'));
            $giftFallbackOfferings = $this->stampVendorReviewSummary($this->stampSourceVersion($giftFallbackOfferings, 'v3'));
            $giftsUnder50 = ProductRanking::sortCollection($giftFallbackProducts->concat($giftFallbackOfferings), 'review_count_desc')
                ->values();
        }

        return $giftsUnder50;
    }

    /**
     * @return array{items:Collection<int, mixed>, total:int, has_more:bool}
     */
    private function giftsUnder50Page(int $page, int $limit): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $items = $this->giftsUnder50();
        $offset = ($page - 1) * $limit;
        $slice = $items->slice($offset, $limit)->values();

        return [
            'items' => $slice,
            'total' => $items->count(),
            'has_more' => ($offset + $limit) < $items->count(),
        ];
    }

    private function giftOfferings(): Collection
    {
        $items = OfferingV3::query()
            ->with(['category', 'type', 'vendor.tiers', 'vendor.user.settings', 'media', 'coverMedia'])
            ->where('status', 'live')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($q): void {
                $q->whereRaw("LOWER(COALESCE(title,'')) like '%gift%'")
                    ->orWhereRaw("LOWER(COALESCE(summary,'')) like '%gift%'")
                    ->orWhereHas('category', function ($cq): void {
                        $cq->whereRaw("LOWER(COALESCE(name,'')) like '%gift%'");
                    })
                    ->orWhereHas('type', function ($tq): void {
                        $tq->whereRaw("LOWER(COALESCE(name,'')) like '%gift%'");
                    });
            })
            ->get()
            ->reject(fn ($offering): bool => EventListing::isPast($offering))
            ->filter(function ($offering): bool {
                $price = ProductRanking::priceValue($offering);
                return $price !== null && $price <= 50.0;
            })
            ->values();

        return $this->stampSourceVersion($items, 'v3');
    }

    private function latestCatalogue(): Collection
    {
        $base = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->with(['media', 'options.values', 'category', 'vendor.tiers', 'vendor.user.settings'])
            ->whereHas('status', function ($qs): void {
                $qs->where('status', 'live');
            });

        $latestLegacyProducts = (clone $base)
            ->latest('id')
            ->limit(6)
            ->get()
            ->reject(fn ($product): bool => EventListing::isPast($product))
            ->map(function ($product) {
                $product->source_version = 'v1-v2';
                $product->catalogue_rank = $product->created_at ? $product->created_at->timestamp : (int) ($product->id ?? 0);
                return $product;
            });

        $latestV3Offerings = OfferingV3::query()
            ->with(['category', 'type', 'vendor.tiers', 'vendor.user.settings', 'media', 'coverMedia'])
            ->where('status', 'live')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->reject(fn ($offering): bool => EventListing::isPast($offering))
            ->map(function ($offering) {
                $offering->source_version = 'v3';
                $offering->created_at = $offering->published_at ?: $offering->created_at;
                $offering->catalogue_rank = $offering->created_at ? $offering->created_at->timestamp : (int) ($offering->id ?? 0);
                return $offering;
            });

        $physicalProducts = $this->latestPhysicalProductsFromOfferingApi();

        return $latestV3Offerings
            ->concat($latestLegacyProducts)
            ->concat($physicalProducts)
            ->reject(fn ($item): bool => EventListing::isPast($item))
            ->sort(function ($left, $right): int {
                $leftVersion = data_get($left, 'source_version') === 'v3' ? 1 : 0;
                $rightVersion = data_get($right, 'source_version') === 'v3' ? 1 : 0;

                if ($leftVersion !== $rightVersion) {
                    return $rightVersion <=> $leftVersion;
                }

                $leftRank = (int) data_get($left, 'catalogue_rank', 0);
                $rightRank = (int) data_get($right, 'catalogue_rank', 0);

                if ($leftRank === $rightRank) {
                    return strcasecmp((string) data_get($left, 'title', ''), (string) data_get($right, 'title', ''));
                }

                return $rightRank <=> $leftRank;
            })
            ->values();
    }

    /**
     * Physical Store products are owned by the Backend. Keep this homepage rail
     * on the same unified offering feed as the Frontend catalogue.
     */
    private function latestPhysicalProductsFromOfferingApi(): Collection
    {
        $backend = rtrim((string) env('BACKEND_URL', env('BACKEND_ASSET_URL', '')), '/');
        if ($backend === '') {
            return collect();
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'Origin' => 'https://www.weofferwellness.co.uk',
                    'Referer' => 'https://www.weofferwellness.co.uk/',
                ])
                ->timeout(8)
                ->get($backend.'/api/offerings', [
                    'version' => 'v3',
                    'sort' => 'newest',
                    'per_page' => 12,
                ]);

            if (! $response->successful()) {
                return collect();
            }

            return collect($response->json('data', []))
                ->filter(fn ($item): bool => data_get($item, 'kind') === 'physical_product' || data_get($item, 'source_type') === 'physical_product')
                ->map(function (array $item): object {
                    $publishedAt = $item['published_at'] ?? null;
                    $createdAt = $publishedAt ?: ($item['created_at'] ?? null);

                    return (object) [
                        'id' => $item['id'] ?? null,
                        'kind' => 'physical_product',
                        'source_type' => 'physical_product',
                        'source_version' => 'v3',
                        'title' => $item['title'] ?? 'Physical product',
                        'summary' => $item['summary'] ?? null,
                        'brand' => $item['brand'] ?? data_get($item, 'vendor.name'),
                        'price' => $item['price'] ?? null,
                        'currency' => $item['currency'] ?? 'GBP',
                        'image' => $item['image_url'] ?? null,
                        'url' => $item['url'] ?? '/products/'.rawurlencode((string) ($item['slug'] ?? '')),
                        'published_at' => $publishedAt,
                        'created_at' => $createdAt,
                        'catalogue_rank' => $createdAt ? Carbon::parse($createdAt)->timestamp : (int) ($item['id'] ?? 0),
                    ];
                })
                ->values();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function comfortRail(float $priceMax, string $groupType, string $mode): Collection
    {
        $limit = 24;
        $pm = max(1, $priceMax);

        $productQuery = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->with(['media','options.values','category', 'vendor.tiers', 'vendor.user.settings'])
            ->where(function ($w): void {
                $w->whereHas('status', function ($qs): void { $qs->whereIn('status', ['live','approved']); });
            });

        if ($mode === 'online') {
            $productQuery->whereHas('options', function ($oq): void {
                $oq->where('meta_name', 'locations')
                   ->whereHas('values', function ($vq): void { $vq->whereRaw("LOWER(value) = 'online'"); });
            });
        } elseif ($mode === 'in-person') {
            $productQuery->whereHas('options', function ($oq): void {
                $oq->where('meta_name', 'locations')
                   ->whereHas('values', function ($vq): void { $vq->whereRaw("LOWER(value) <> 'online'"); });
            });
        }

        if ($groupType) {
            ProductSearchFilters::applyWhoFilter($productQuery, null, $groupType);
        }

        $productQuery->where(function ($qq) use ($pm): void {
            $qq->where(function ($qp) use ($pm): void { $qp->where('price','<=',$pm)->orWhere('price','<=',$pm*100); })
               ->orWhere(function ($qp) use ($pm): void { $qp->where('price','<=',$pm*100); })
               ->orWhereHas('variants', function ($qv) use ($pm): void {
                   $qv->where(function ($qq2) use ($pm): void { $qq2->where('price','<=',$pm)->orWhere('price','<=',$pm*100); })
                      ->orWhere(function ($qq2) use ($pm): void { $qq2->where('price','<=',$pm*100); });
               });
        });

        $products = $productQuery->get()
            ->reject(fn ($product): bool => EventListing::isPast($product))
            ->filter(function ($product) use ($pm): bool {
            $min = $product->variants_min_price ?? $product->price;
            if (! is_numeric($min)) {
                return false;
            }

            $min = (float) $min;
            if ($min >= 1000) {
                $min = $min / 100;
            }

            return $min <= $pm;
        })->values();

        $offerings = OfferingV3::query()
            ->with(['category', 'type', 'vendor.tiers', 'vendor.user.settings', 'media', 'coverMedia'])
            ->whereIn('status', ['live', 'approved'])
            ->get()
            ->reject(fn ($offering): bool => EventListing::isPast($offering))
            ->filter(function ($offering) use ($pm, $mode, $groupType): bool {
                if (method_exists($offering, 'hasDisplayableImage') && ! $offering->hasDisplayableImage()) {
                    return false;
                }

                $price = ProductRanking::priceValue($offering);
                if ($price === null || $price > $pm) {
                    return false;
                }

                $locations = method_exists($offering, 'getLocations') ? $offering->getLocations() : [];
                $hasOnline = in_array('Online', $locations, true);
                $physical = array_values(array_filter($locations, fn ($location) => $location !== 'Online'));

                if (! $this->offeringMatchesGroupType($offering, $groupType)) {
                    return false;
                }

                if ($mode === 'online') {
                    return $hasOnline && count($physical) === 0;
                }

                if ($mode === 'in-person') {
                    return count($physical) > 0;
                }

                return true;
            })
            ->values();

        $products = $this->stampSourceVersion($products, 'v1-v2');
        $offerings = $this->stampSourceVersion($offerings, 'v3');

        return ProductRanking::sortCollection($products->concat($offerings))
            ->take($limit)
            ->values();
    }

    private function offeringMatchesGroupType(OfferingV3 $offering, ?string $groupType): bool
    {
        $groupType = strtolower(trim((string) $groupType));
        if (! in_array($groupType, ['solo', 'couple', 'group'], true)) {
            return true;
        }

        $rows = \DB::table('offering_price_options')
            ->where('offering_id', $offering->id)
            ->select(['audience_type', 'pricing_type'])
            ->get();

        if ($rows->isNotEmpty()) {
            foreach ($rows as $row) {
                $audience = strtolower(trim((string) ($row->audience_type ?? '')));
                $pricing = strtolower(trim((string) ($row->pricing_type ?? '')));

                if ($groupType === 'solo' && ($audience === 'solo' || str_contains($pricing, 'solo'))) {
                    return true;
                }

                if ($groupType === 'couple' && ($audience === 'couple' || str_contains($pricing, 'couple'))) {
                    return true;
                }

                if ($groupType === 'group' && ($audience === 'group' || str_contains($pricing, 'group'))) {
                    return true;
                }
            }
        }

        $haystack = strtolower(trim(implode(' ', array_filter([
            (string) ($offering->title ?? ''),
            (string) ($offering->summary ?? ''),
            (string) ($offering->type?->name ?? ''),
            (string) ($offering->category?->name ?? ''),
        ]))));

        if ($groupType === 'solo') {
            return str_contains($haystack, 'solo') || str_contains($haystack, '1 person') || str_contains($haystack, '1-to-1') || str_contains($haystack, '1:1');
        }

        if ($groupType === 'couple') {
            return str_contains($haystack, 'couple') || str_contains($haystack, '2 person') || str_contains($haystack, 'pair') || str_contains($haystack, 'duo');
        }

        return str_contains($haystack, 'group') || str_contains($haystack, 'group session') || str_contains($haystack, 'workshop') || str_contains($haystack, 'class');
    }
}
