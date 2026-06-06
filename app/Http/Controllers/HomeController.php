<?php

namespace App\Http\Controllers;

use App\Models\OfferingV3;
use App\Models\Product;
use App\Models\Review;
use App\Support\EventListing;
use App\Support\ProductRanking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $cacheVersion = $this->homeCacheVersion();

        if (request()->boolean('fresh')) {
            Cache::forget('home:index:v1');
            Cache::forget('home:index:v2');
            Cache::forget('home:index:v3');
            Cache::forget('home:index:html:v1');
            Cache::forget('home:index:html:v2');
            Cache::forget('home:index:html:v3');
            Cache::forget('home:index:v3:'.$cacheVersion);
            Cache::forget('home:index:html:v3:'.$cacheVersion);
        }

        $payload = Cache::remember('home:index:v3:'.$cacheVersion, now()->addMinutes(10), function () {
            $giftsUnder50 = collect();
            $onlineUnder50 = collect();
            $latestCatalogue = collect();
            $hasClassesThisWeek = false;
            $reviewQuery = Review::query()->whereRaw("TRIM(COALESCE(review_text, '')) <> ''");
            $reviewCount = (int) (clone $reviewQuery)->count();
            $avgRating = (float) ((clone $reviewQuery)->avg('rating') ?? 0);

            try {
                $base = Product::query()
                    ->withCount('reviews')
                    ->withAvg('reviews', 'rating')
                    ->withMin('variants', 'price')
                    ->with(['media', 'options.values', 'category', 'vendor.tiers', 'vendor.user.settings'])
                    ->whereHas('status', function ($qs) {
                        $qs->where('status', 'live');
                    });

                $giftProducts = (clone $base)
                    ->where(function ($q) {
                        $q->whereRaw("LOWER(COALESCE(tags_list,'')) like '%gift%'")
                            ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%voucher%'")
                            ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%card%'")
                            ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%present%'")
                            ->orWhereRaw("LOWER(COALESCE(product_type,'')) like '%gift%'");
                    })
                    ->where(function ($q) {
                        $q->where(function ($inner) {
                            $inner->where('price', '<', 1000)->where('price', '<=', 50);
                        })
                            ->orWhere(function ($inner) {
                                $inner->where('price', '>=', 1000)->where('price', '<=', 50 * 100);
                            })
                            ->orWhereHas('variants', function ($qv) {
                                $qv->where(function ($qq) {
                                    $qq->where('price', '<', 1000)->where('price', '<=', 50);
                                })
                                    ->orWhere(function ($qq) {
                                        $qq->where('price', '>=', 1000)->where('price', '<=', 50 * 100);
                                    });
                            });
                    })
                    ->get()
                    ->reject(fn ($p) => EventListing::isPast($p))
                    ->filter(function ($p) {
                        $min = $p->variants_min_price ?? $p->price;
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

                $giftOfferings = $this->homeGiftOfferings();
                $giftsUnder50 = ProductRanking::sortCollection($giftProducts->concat($giftOfferings), 'review_count_desc')
                    ->take(12)
                    ->values();

                if ($giftsUnder50->isEmpty()) {
                    $giftFallbackProducts = (clone $base)
                        ->where(function ($q) {
                            $q->where(function ($inner) {
                                $inner->where('price', '<', 1000)->where('price', '<=', 50);
                            })
                                ->orWhere(function ($inner) {
                                    $inner->where('price', '>=', 1000)->where('price', '<=', 50 * 100);
                                })
                                ->orWhereHas('variants', function ($qv) {
                                    $qv->where(function ($qq) {
                                        $qq->where('price', '<', 1000)->where('price', '<=', 50);
                                    })
                                        ->orWhere(function ($qq) {
                                            $qq->where('price', '>=', 1000)->where('price', '<=', 50 * 100);
                                        });
                                });
                        })
                        ->get()
                        ->reject(fn ($p) => EventListing::isPast($p))
                        ->filter(function ($p) {
                            $min = $p->variants_min_price ?? $p->price;
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
                        ->filter(function ($offering) {
                            $min = ProductRanking::priceValue($offering);

                            return $min !== null && $min <= 50.0;
                        })
                        ->values();

                    $giftsUnder50 = ProductRanking::sortCollection($giftFallbackProducts->concat($giftFallbackOfferings), 'review_count_desc')
                        ->take(12)
                        ->values();
                }

                $onlineProducts = (clone $base)
                    ->whereHas('options', function ($q) {
                        $q->where('meta_name', 'locations')
                            ->whereHas('values', function ($q2) {
                                $q2->whereRaw('LOWER(value) = ?', ['online']);
                            });
                    })
                    ->where(function ($q) {
                        $q->where(function ($inner) {
                            $inner->where('price', '<', 1000)->where('price', '<=', 50);
                        })
                            ->orWhere(function ($inner) {
                                $inner->where('price', '>=', 1000)->where('price', '<=', 50 * 100);
                            })
                            ->orWhereHas('variants', function ($qv) {
                                $qv->where(function ($qq) {
                                    $qq->where('price', '<', 1000)->where('price', '<=', 50);
                                })
                                    ->orWhere(function ($qq) {
                                        $qq->where('price', '>=', 1000)->where('price', '<=', 50 * 100);
                                    });
                            });
                    })
                    ->get()
                    ->reject(fn ($p) => EventListing::isPast($p))
                    ->filter(function ($p) {
                        $min = $p->variants_min_price ?? $p->price;
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

                $onlineOfferings = $this->homeOnlineOfferings();
                $onlineUnder50 = ProductRanking::sortCollection($onlineProducts->concat($onlineOfferings))
                    ->take(12)
                    ->values();

                $latestLegacyProducts = (clone $base)
                    ->latest('id')
                    ->limit(6)
                    ->get()
                    ->reject(fn ($product) => EventListing::isPast($product))
                    ->map(function ($product) {
                        $product->source_version = 'legacy';
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
                    ->reject(fn ($offering) => EventListing::isPast($offering))
                    ->map(function ($offering) {
                        $offering->source_version = 'v3';
                        $offering->created_at = $offering->published_at ?: $offering->created_at;
                        $offering->catalogue_rank = $offering->created_at ? $offering->created_at->timestamp : (int) ($offering->id ?? 0);

                        return $offering;
                    });

                $latestCatalogue = $latestV3Offerings
                    ->concat($latestLegacyProducts)
                    ->reject(fn ($item) => EventListing::isPast($item))
                    ->sort(function ($left, $right) {
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

                $weekStart = now()->startOfWeek();
                $weekEnd = now()->endOfWeek();
                $hasClassesThisWeek = false;
                try {
                    $classes = (clone $base)
                        ->whereRaw("LOWER(COALESCE(product_type,'')) like '%class%'")
                        ->latest('id')
                        ->limit(120)
                        ->get();
                    foreach ($classes as $p) {
                        $meta = $p->meta_json ?? [];
                        $date = $meta['date'] ?? null;
                        $start = $meta['start_date'] ?? null;
                        $end = $meta['end_date'] ?? null;
                        $d = $date ? Carbon::parse((string) $date) : null;
                        $s = $start ? Carbon::parse((string) $start) : null;
                        $e = $end ? Carbon::parse((string) $end) : null;
                        if ($d && $d->between($weekStart, $weekEnd)) {
                            $hasClassesThisWeek = true;
                            break;
                        }
                        if ($s || $e) {
                            $rs = $s ?: $e;
                            $re = $e ?: $s;
                            if ($rs && $re) {
                                if ($rs <= $weekEnd && $re >= $weekStart) {
                                    $hasClassesThisWeek = true;
                                    break;
                                }
                            } elseif ($rs) {
                                if ($rs->between($weekStart, $weekEnd)) {
                                    $hasClassesThisWeek = true;
                                    break;
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $hasClassesThisWeek = false;
                }
            } catch (\Throwable $e) {
                $giftsUnder50 = collect();
                $onlineUnder50 = collect();
                $latestCatalogue = collect();
                $hasClassesThisWeek = false;
            }

            return [
                'giftsUnder50' => $giftsUnder50,
                'onlineUnder50' => $onlineUnder50,
                'latestCatalogue' => $latestCatalogue,
                'hasClassesThisWeek' => $hasClassesThisWeek,
                'review_count' => $reviewCount,
                'verified_count' => $reviewCount,
                'avg_rating' => $avgRating > 0 ? round($avgRating, 1) : null,
            ];
        });

        if (app()->environment('local') || auth()->check()) {
            return view('home.index', $payload);
        }

        $html = Cache::remember('home:index:html:v3:'.$cacheVersion, now()->addMinutes(10), function () use ($payload) {
            return view('home.index', $payload)->render();
        });

        return response($html)
            ->header('Cache-Control', 'public, max-age=600');
    }

    private function homeGiftOfferings(): \Illuminate\Support\Collection
    {
        return OfferingV3::query()
            ->with(['category', 'type', 'vendor.tiers', 'vendor.user.settings', 'media', 'coverMedia'])
            ->where('status', 'live')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereRaw("LOWER(COALESCE(title,'')) like '%gift%'")
                    ->orWhereRaw("LOWER(COALESCE(summary,'')) like '%gift%'")
                    ->orWhereHas('category', function ($cq) {
                        $cq->whereRaw("LOWER(COALESCE(name,'')) like '%gift%'");
                    })
                    ->orWhereHas('type', function ($tq) {
                        $tq->whereRaw("LOWER(COALESCE(name,'')) like '%gift%'");
                    });
            })
            ->get()
            ->reject(fn ($offering) => EventListing::isPast($offering))
            ->filter(function ($offering) {
                $price = ProductRanking::priceValue($offering);

                return $price !== null && $price <= 50.0;
            })
            ->values();
    }

    private function homeOnlineOfferings(): \Illuminate\Support\Collection
    {
        return OfferingV3::query()
            ->with(['category', 'type', 'vendor.tiers', 'vendor.user.settings', 'media', 'coverMedia'])
            ->where('status', 'live')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get()
            ->reject(fn ($offering) => EventListing::isPast($offering))
            ->filter(function ($offering) {
                $locations = method_exists($offering, 'getLocations') ? $offering->getLocations() : [];
                $hasOnline = in_array('Online', $locations, true);
                $physical = array_values(array_filter($locations, fn ($location) => $location !== 'Online'));
                $price = ProductRanking::priceValue($offering);

                return $hasOnline && count($physical) === 0 && $price !== null && $price <= 50.0;
            })
            ->values();
    }

    private function homeCacheVersion(): string
    {
        $templateFingerprint = implode('|', array_map(
            static function (string $path): string {
                return $path . ':' . (is_file($path) ? (string) filemtime($path) : 'missing');
            },
            [
                __FILE__,
                resource_path('views/home/index.blade.php'),
                resource_path('views/home/sections/gifts.blade.php'),
                resource_path('views/home/sections/trust-feel-safe.blade.php'),
                resource_path('views/partials/product_showcase_section.blade.php'),
                public_path('build/manifest.json'),
                resource_path('js/home-offerings.js'),
            ]
        ));

        return sha1(implode('|', [
            (string) (Product::query()->max('updated_at') ?? ''),
            (string) (OfferingV3::query()->max('updated_at') ?? ''),
            (string) (Review::query()->max('updated_at') ?? ''),
            $templateFingerprint,
        ]));
    }
}
