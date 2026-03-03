<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\Review;

class HomeController extends Controller
{
    public function index()
    {
        if (request()->boolean('fresh')) {
            Cache::forget('home:index:v1');
            Cache::forget('home:index:html:v1');
        }

        $payload = Cache::remember('home:index:v1', now()->addMinutes(10), function () {
            $giftsUnder50 = collect();
            $onlineUnder50 = collect();
            $hasClassesThisWeek = false;

            try {
                $base = Product::query()
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->withMin('variants', 'price')
                ->with(['media', 'options.values', 'category'])
                ->whereHas('status', function ($qs) {
                    $qs->where('status', 'live');
                });

            $giftsUnder50 = (clone $base)
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
                ->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
                ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
                ->orderByRaw('COALESCE(reviews_count, 0) DESC')
                ->limit(12)
                ->get()
                ->filter(function ($p) {
                    $min = $p->variants_min_price ?? $p->price;
                    if (!is_numeric($min)) return false;
                    $min = (float) $min;
                    if ($min >= 1000) $min = $min / 100;
                    return $min <= 50.0;
                })
                ->values();

            if ($giftsUnder50->isEmpty()) {
                $giftsUnder50 = (clone $base)
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
                    ->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
                    ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
                    ->orderByRaw('COALESCE(reviews_count, 0) DESC')
                    ->limit(12)
                    ->get()
                    ->filter(function ($p) {
                        $min = $p->variants_min_price ?? $p->price;
                        if (!is_numeric($min)) return false;
                        $min = (float) $min;
                        if ($min >= 1000) $min = $min / 100;
                        return $min <= 50.0;
                    })
                    ->values();
            }

            $onlineUnder50 = (clone $base)
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
                ->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
                ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
                ->orderByRaw('COALESCE(reviews_count, 0) DESC')
                ->limit(12)
                ->get()
                ->filter(function ($p) {
                    $min = $p->variants_min_price ?? $p->price;
                    if (!is_numeric($min)) return false;
                    $min = (float) $min;
                    if ($min >= 1000) $min = $min / 100;
                    return $min <= 50.0;
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
                    if ($d && $d->between($weekStart, $weekEnd)) { $hasClassesThisWeek = true; break; }
                    if ($s || $e) {
                        $rs = $s ?: $e;
                        $re = $e ?: $s;
                        if ($rs && $re) {
                            if ($rs <= $weekEnd && $re >= $weekStart) { $hasClassesThisWeek = true; break; }
                        } elseif ($rs) {
                            if ($rs->between($weekStart, $weekEnd)) { $hasClassesThisWeek = true; break; }
                        }
                    }
                }
            } catch (\Throwable $e) { $hasClassesThisWeek = false; }
            } catch (\Throwable $e) {
                $giftsUnder50 = collect();
                $onlineUnder50 = collect();
                $hasClassesThisWeek = false;
            }

            return [
                'giftsUnder50' => $giftsUnder50,
                'onlineUnder50' => $onlineUnder50,
                'hasClassesThisWeek' => $hasClassesThisWeek,
                'review_count' => (int) (Review::query()->count() ?? 0),
                'avg_rating' => ($r = (float) (Review::query()->avg('rating') ?? 0)) > 0 ? round($r, 1) : null,
            ];
        });

        if (app()->environment('local')) {
            return view('home.index', $payload);
        }

        $html = Cache::remember('home:index:html:v1', now()->addMinutes(10), function () use ($payload) {
            return view('home.index', $payload)->render();
        });

        return response($html)
            ->header('Cache-Control', 'public, max-age=600');
    }
}
