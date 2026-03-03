<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $format = strtolower((string) $request->query('format', ''));
        if ($format === 'online') {
            return redirect('/classes?mode=online&anytime=1', 302);
        }

        if ($request->has('category')) {
            $raw = trim((string) $request->query('category'));
            if ($raw !== '') {
                $slug = Str::slug($raw);
                $type = strtolower((string) $request->query('type', 'therapies'));
                $allowed = ['therapies','events','workshops','classes','retreats','gifts'];
                if (!in_array($type, $allowed, true)) { $type = 'therapies'; }
                return redirect('/'.$type.'/'.$slug, 302);
            }
        }

        if ($format && !$request->has('mode')) {
            $qs = $request->query();
            $qs['mode'] = $format;
            unset($qs['format']);
            $to = url('/search') . '?' . http_build_query($qs);
            return redirect($to, 302);
        }

        // Server-rendered results to support Blade product card + map
        $base = \App\Models\Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->withMax('variants', 'price')
            ->with(['media', 'options.values', 'category', 'status', 'vendor.locations']);

        $query = clone $base;
        // Only visible products
        $query->where(function($q){
            $q->whereHas('status', function($qs){ $qs->whereIn('status', ['live','approved']); })
              ->orWhereNull('product_status_id');
        });

        $what = $request->string('what')->toString();
        if ($what) {
            $pattern = "%{$what}%";
            $query->where(function ($q) use ($pattern) {
                $q->where('title', 'like', $pattern)
                  ->orWhere('summary', 'like', $pattern)
                  ->orWhere('body_html', 'like', $pattern)
                  ->orWhere('what_to_expect', 'like', $pattern)
                  ->orWhere('included', 'like', $pattern)
                  ->orWhere('tags_list', 'like', $pattern);
            });
        }

        // Type filter
        $type = $request->string('type')->toString();
        if ($type) {
            $this->applyTypeFilter($query, $type);
        }

        // Tag filter
        if ($tag = $request->string('tag')->toString()) {
            $lc = strtolower($tag);
            if (in_array($lc, ['gift','gifts'], true)) {
                $query->where(function($q){
                    $q->whereRaw("LOWER(COALESCE(tags_list,'')) like '%gift%'")
                      ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%voucher%'")
                      ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%card%'")
                      ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%present%'");
                });
            } else {
                $safe = str_replace(['%','_'], ['\\%','\\_'], $lc);
                $query->whereRaw("LOWER(COALESCE(tags_list,'')) like ?", ['%'.$safe.'%']);
            }
        }

        // Mode (online/in-person)
        $modeInput = $request->filled('mode') ? $request->string('mode')->toString() : $request->string('format')->toString();
        if ($modeInput) {
            $mode = strtolower($modeInput);
            $query->whereHas('options', function ($q) use ($mode) {
                $q->where('meta_name', 'locations')
                  ->whereHas('values', function ($q2) use ($mode) {
                      if ($mode === 'online') { $q2->whereRaw('LOWER(value) = ?', ['online']); }
                      else { $q2->whereRaw('LOWER(value) != ?', ['online']); }
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
            $query->where(function($q) use ($pm) {
                $q->where(function($qp) use ($pm){ $qp->where('price', '<', 1000)->where('price', '<=', $pm); })
                  ->orWhere(function($qp) use ($pm){ $qp->where('price', '>=', 1000)->where('price', '<=', $pm * 100); })
                  ->orWhereHas('variants', function($qv) use ($pm){
                      $qv->where(function($qq) use ($pm){ $qq->where('price', '<', 1000)->where('price', '<=', $pm); })
                         ->orWhere(function($qq) use ($pm){ $qq->where('price', '>=', 1000)->where('price', '<=', $pm * 100); });
                  });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int)$request->input('category_id'));
        } elseif ($request->filled('category')) {
            $cat = strtolower((string) $request->input('category'));
            $like = '%'.$cat.'%';
            $ids = \App\Models\ProductCategory::query()
                ->whereRaw('LOWER(name) LIKE ?', [$like])
                ->pluck('id')->all();
            if (!empty($ids)) $query->whereIn('category_id', $ids);
        }

        if ($request->boolean('anytime')) {
            $query->whereRaw("(JSON_EXTRACT(meta_json, '$.date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.date')) = '')")
                  ->whereRaw("(JSON_EXTRACT(meta_json, '$.start_date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.start_date')) = '')")
                  ->whereRaw("(JSON_EXTRACT(meta_json, '$.end_date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.end_date')) = '')");
        }

        // Sort
        $sort = $request->string('sort', 'popular')->toString();
        if ($sort === 'newest') $query->latest('id');
        elseif ($sort === 'price_asc') $query->orderBy('price', 'asc');
        elseif ($sort === 'price_desc') $query->orderBy('price', 'desc');
        else {
            $query->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
                  ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
                  ->orderByRaw('COALESCE(reviews_count, 0) DESC');
        }

        $perPage = (int) $request->integer('per_page', 48);
        $perPage = min(96, max(12, $perPage));
        $products = $query->paginate($perPage)->withQueryString();

        return view('search.index', [
            'mapsKey' => env('GOOGLE_MAPS_API_KEY'),
            'products' => $products,
            'resultCount' => method_exists($products, 'total') ? $products->total() : $products->count(),
            'perPage' => $perPage,
        ]);
    }

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
            $safe = str_replace(['%','_'], ['\\%','\\_'], $lc);
            $query->whereRaw("LOWER(COALESCE(product_type,'')) like ?", ['%'.$safe.'%']);
        }
    }
}
