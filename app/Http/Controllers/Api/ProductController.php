<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $base = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->withMax('variants', 'price')
            ->with(['media', 'options.values', 'category']);

        $what = $request->string('what')->toString();
        $applySearch = function ($query) use ($what) {
            if (!$what) {
                return;
            }

            $query->where(function ($q) use ($what) {
                $pattern = "%{$what}%";
                $q->where('title', 'like', $pattern)
                  ->orWhere('summary', 'like', $pattern)
                  ->orWhere('body_html', 'like', $pattern)
                  ->orWhere('what_to_expect', 'like', $pattern)
                  ->orWhere('included', 'like', $pattern)
                  ->orWhere('tags_list', 'like', $pattern);
            });
        };

        // Filters
        $query = clone $base;
        // Only visible products: include live/approved OR legacy rows without status set
        $query->where(function($q){
            $q->whereHas('status', function($qs){ $qs->whereIn('status', ['live','approved']); })
              ->orWhereNull('product_status_id');
        });
        $applySearch($query);

        $modeInput = $request->filled('mode') ? $request->string('mode')->toString() : $request->string('format')->toString();
        Log::info('search.products', [
            'what' => $what,
            'where' => $request->input('where'),
            'mode' => $modeInput,
            'type' => $request->input('type'),
            'user_id' => optional($request->user())->id,
        ]);

        if ($type = $request->string('type')->toString()) {
            // Apply a flexible type filter that mirrors LandingController
            // to avoid over-restricting to exact product_type values
            $this->applyTypeFilter($query, $type);
        }

        $tag = $request->string('tag')->toString();
        if ($tag) {
            $lc = strtolower($tag);
            if (in_array($lc, ['gift','gifts'], true)) {
                // Treat "gift" broadly: gift, gifts, voucher, card, present
                $query->where(function($q){
                    $q->whereRaw("LOWER(COALESCE(tags_list,'')) like '%gift%'")
                      ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%voucher%'")
                      ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%card%'")
                      ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%present%'");
                });
            } else {
                $safe = str_replace(['%','_'], ['\\%','\\_'], strtolower($tag));
                $query->whereRaw("LOWER(COALESCE(tags_list,'')) like ?", ['%'.$safe.'%']);
            }
        }

        // Mode (Online / In-person) derived from options meta 'locations'
        // Accept alias: format=online|in-person
        $mode = $modeInput;
        if ($mode) {
            $mode = strtolower($mode);
            $query->whereHas('options', function ($q) use ($mode) {
                $q->where('meta_name', 'locations')
                  ->whereHas('values', function ($q2) use ($mode) {
                      if ($mode === 'online') {
                          $q2->whereRaw('LOWER(value) = ?', ['online']);
                      } else {
                          $q2->whereRaw('LOWER(value) != ?', ['online']);
                      }
                  });
            });
        }

        if ($where = $request->string('where')->toString()) {
            $places = collect(preg_split('/[|,]/', $where))
                ->map(fn($part) => trim((string) $part))
                ->filter();
            if ($places->isNotEmpty()) {
                $query->where(function($outer) use ($places) {
                    foreach ($places as $place) {
                        $outer->orWhereHas('options', function ($q) use ($place) {
                            $q->where('meta_name', 'locations')
                              ->whereHas('values', function ($q2) use ($place) {
                                  $q2->where('value', 'like', "%{$place}%");
                              });
                        });
                    }
                });
            }
        }

        if ($request->filled('price_max')) {
            $pm = (float) $request->input('price_max');
            // Support prices stored in pennies (<= pm*100) or pounds (<= pm)
            // Use variants relation (no reliance on select aliases)
            $query->where(function($q) use ($pm) {
                $q->where('price', '<=', $pm)
                  ->orWhere('price', '<=', $pm * 100)
                  ->orWhereHas('variants', function($qv) use ($pm){
                      $qv->where('price', '<=', $pm)
                         ->orWhere('price', '<=', $pm * 100);
                  });
            });
        }

        // Sorting
        $sort = $request->string('sort', 'popular')->toString();
        if ($sort === 'newest') {
            $query->latest('id');
        } elseif ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            // popular/favorability: rank by weighted combo of rating × log(review_count)
            // This pushes highly rated items with many reviews to the top, and
            // still bubbles up new items with great ratings.
            $query->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
                  ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
                  ->orderByRaw('COALESCE(reviews_count, 0) DESC');
        }

        // Category filter: by id or by name/slug via `category`
        if ($request->filled('category_id')) {
            $query->where('category_id', (int)$request->input('category_id'));
        } elseif ($request->filled('category')) {
            $cat = trim((string) $request->input('category'));
            if (is_numeric($cat)) {
                $query->where('category_id', (int)$cat);
            } else {
                $like = '%'.strtolower($cat).'%';
                $ids = \App\Models\ProductCategory::query()
                    ->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->pluck('id')->all();
                if (!empty($ids)) {
                    $query->whereIn('category_id', $ids);
                }
            }
        }

        // Anytime filter: products with no scheduled date/time
        if ($request->boolean('anytime')) {
            $query->whereRaw("(JSON_EXTRACT(meta_json, '$.date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.date')) = '')")
                  ->whereRaw("(JSON_EXTRACT(meta_json, '$.start_date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.start_date')) = '')")
                  ->whereRaw("(JSON_EXTRACT(meta_json, '$.end_date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.end_date')) = '')");
        }

        $limit = (int) $request->integer('limit', 50);
        $products = $query->limit($limit)->get();
        // Fallback: if filtering by tag + price yields nothing, retry without tag
        if ($products->isEmpty() && $tag && $request->filled('price_max')) {
            $retry = clone $base;
            $retry->where(function($q){
                $q->whereHas('status', function($qs){ $qs->whereIn('status', ['live','approved']); })
                  ->orWhereNull('product_status_id');
            });
            $applySearch($retry);
            if ($type = $request->string('type')->toString()) {
                $this->applyTypeFilter($retry, $type);
            }
            if ($modeInput) {
                $mode = strtolower($modeInput);
                $retry->whereHas('options', function ($q) use ($mode) {
                    $q->where('meta_name', 'locations')
                      ->whereHas('values', function ($q2) use ($mode) {
                          if ($mode === 'online') {
                              $q2->whereRaw('LOWER(value) = ?', ['online']);
                          } else {
                              $q2->whereRaw('LOWER(value) != ?', ['online']);
                          }
                      });
                });
            }
            if ($where = $request->string('where')->toString()) {
                $places = collect(preg_split('/[|,]/', $where))
                    ->map(fn($part) => trim((string) $part))
                    ->filter();
                if ($places->isNotEmpty()) {
                    $retry->where(function($outer) use ($places) {
                        foreach ($places as $place) {
                            $outer->orWhereHas('options', function ($q) use ($place) {
                                $q->where('meta_name', 'locations')
                                  ->whereHas('values', function ($q2) use ($place) {
                                      $q2->where('value', 'like', "%{$place}%");
                                  });
                            });
                        }
                    });
                }
            }
            if ($request->filled('price_max')) {
                $pm = (float) $request->input('price_max');
                $retry->where(function($q) use ($pm) {
                    $q->where('price', '<=', $pm)
                      ->orWhere('price', '<=', $pm * 100)
                      ->orWhereHas('variants', function($qv) use ($pm){
                          $qv->where('price', '<=', $pm)
                             ->orWhere('price', '<=', $pm * 100);
                      });
                });
            }
            if ($request->filled('category_id')) {
                $retry->where('category_id', (int)$request->input('category_id'));
            }
            // Keep sorting consistent
            $sort = $request->string('sort', 'popular')->toString();
            if ($sort === 'newest') {
                $retry->latest('id');
            } elseif ($sort === 'price_asc') {
                $retry->orderBy('price', 'asc');
            } elseif ($sort === 'price_desc') {
                $retry->orderBy('price', 'desc');
            } else {
                $retry->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
                      ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
                      ->orderByRaw('COALESCE(reviews_count, 0) DESC');
            }

            $products = $retry->limit($limit)->get();
        }

        // Transform
        $items = $products->map(function (Product $p) {
            $locations = $p->getLocations();
            $isOnline = in_array('Online', $locations, true);
            $physicalLocations = array_values(array_filter($locations, fn($l) => $l !== 'Online'));

            $meta = $p->meta_json ?? [];
            $lat = $meta['lat'] ?? null;
            $lng = $meta['lng'] ?? null;
            $date = $meta['date'] ?? null;
            $start = $meta['start_date'] ?? null;
            $end = $meta['end_date'] ?? null;

            // Map type to URL segment
            $t = strtolower((string) $p->product_type);
            $tags = strtolower((string) $p->tags_list);
            $typeSeg = 'therapies';
            if (str_contains($t, 'workshop')) $typeSeg = 'workshops';
            elseif (str_contains($t, 'event')) $typeSeg = 'events';
            elseif (str_contains($t, 'class')) $typeSeg = 'classes';
            elseif (str_contains($t, 'retreat')) $typeSeg = 'retreats';
            elseif (str_contains($t, 'gift') || str_contains($tags, 'gift')) $typeSeg = 'gifts';
            $slug = Str::slug($p->title ?: (string)$p->id);
            return [
                'id' => $p->id,
                'title' => $p->title,
                'type' => $p->product_type ?: 'experience',
                'category' => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
                'mode' => $isOnline && count($physicalLocations) === 0 ? 'Online' : (count($physicalLocations) ? 'In-person' : null),
                'location' => $physicalLocations[0] ?? ($isOnline ? 'Online' : null),
                'locations' => $locations,
                'lat' => is_numeric($lat) ? (float)$lat : null,
                'lng' => is_numeric($lng) ? (float)$lng : null,
                'date' => $date,
                'start_date' => $start,
                'end_date' => $end,
                'price' => $p->price ?? null,
                'price_min' => $p->variants_min_price ?? ($p->price ?? null),
                'price_max' => $p->variants_max_price ?? ($p->price ?? null),
                'compare_at_price' => $meta['compare_at_price'] ?? null,
                'currency' => $meta['currency'] ?? 'GBP',
                'rating' => round((float)($p->reviews_avg_rating ?? 0), 1) ?: null,
                'review_count' => (int)($p->reviews_count ?? 0),
                'image' => $p->getFirstImageUrl(),
                'tags' => $p->tags_list ? array_map('trim', explode(',', $p->tags_list)) : [],
                'url' => url('/'.$typeSeg.'/' . $p->id . '-' . $slug),
            ];
        });

        return response()->json($items);
    }

    // Flexible type filtering consistent with LandingController
    private function applyTypeFilter($query, ?string $type): void
    {
        if (!$type) return;
        $lc = strtolower(trim($type));
        if ($lc === 'events' || $lc === 'event') {
            $query->where(function($q){
                $q->whereRaw("LOWER(product_type) like '%event%'")
                  ->orWhereRaw("LOWER(product_type) like '%workshop%'")
                  ->orWhereNotNull('meta_json->date')
                  ->orWhereNotNull('meta_json->start_date');
            });
        } elseif ($lc === 'workshops' || $lc === 'workshop') {
            $query->whereRaw("LOWER(product_type) like '%workshop%'");
        } elseif ($lc === 'classes' || $lc === 'class') {
            $query->whereRaw("LOWER(product_type) like '%class%'");
        } elseif (in_array($lc, ['therapies','therapy','experience','experiences'], true)) {
            $query->where(function($q){
                $q->whereRaw("LOWER(COALESCE(product_type,'')) not like '%event%'")
                  ->whereRaw("LOWER(COALESCE(product_type,'')) not like '%workshop%'")
                  ->whereRaw("LOWER(COALESCE(product_type,'')) not like '%class%'");
            });
        } elseif ($lc === 'retreats' || $lc === 'retreat') {
            $query->whereRaw("LOWER(product_type) like '%retreat%'");
        } elseif ($lc === 'gifts' || $lc === 'gift') {
            $query->where(function($q){
                $q->whereRaw("LOWER(COALESCE(tags_list,'')) like '%gift%'")
                  ->orWhereRaw("LOWER(COALESCE(product_type,'')) like '%gift%'");
            });
        } else {
            // Fallback: substring match on product_type
            $safe = str_replace(['%','_'], ['\\%','\\_'], $lc);
            $query->whereRaw("LOWER(COALESCE(product_type,'')) like ?", ['%'.$safe.'%']);
        }
    }
}
