<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class LandingController extends Controller
{
    // Supported hubs/types
    private const TYPES = ['therapies','events','workshops','classes','retreats','gifts'];

    public function hub(Request $request, string $type)
    {
        $type = strtolower($type);
        if (!in_array($type, self::TYPES, true) && $type !== 'near-me') {
            abort(404);
        }

        // Top categories by product count (optionally filtered by type hint)
        $categories = ProductCategory::query()
            ->withCount(['products as products_count' => function ($q) use ($type) {
                $this->applyTypeFilter($q, $type);
            }])
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->take(24)
            ->get()
            ->map(function ($cat) use ($type) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $this->slugify($cat->name),
                    'count' => (int)($cat->products_count ?? 0),
                    'url' => url("/{$type}/".$this->slugify($cat->name)),
                ];
            })->values();

        // Fallback if counts are zero (some schemas may not link correctly)
        if ($categories->sum('count') === 0) {
            try {
                $agg = Product::query();
                $this->applyTypeFilter($agg, $type);
                $agg = $agg->selectRaw('category_id, COUNT(*) as cnt')
                           ->whereNotNull('category_id')
                           ->groupBy('category_id')
                           ->orderByDesc('cnt')
                           ->limit(24)
                           ->get();
                $ids = $agg->pluck('category_id')->filter()->unique()->values();
                $map = ProductCategory::query()->whereIn('id', $ids)->get()->keyBy('id');
                $categories = $agg->map(function($row) use ($map, $type){
                    $cat = $map->get($row->category_id);
                    if (!$cat) return null;
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $this->slugify($cat->name),
                        'count' => (int)($row->cnt ?? 0),
                        'url' => url("/{$type}/".$this->slugify($cat->name)),
                    ];
                })->filter()->values();
            } catch (\Throwable $e) {
                // keep empty categories; frontend will hide the section
            }
        }

        // Products for the hub with pagination
        $perPage = (int) $request->integer('per_page', $type === 'therapies' ? 60 : 24);
        $perPage = max(6, min($perPage, 120));
        $page = (int) $request->integer('page', 1);
        $cookieCity = trim((string) $request->cookie('wow_city', ''));

        $geoStatus = $type === 'near-me'
            ? ($cookieCity !== '' ? 'ready' : 'needs-location')
            : null;

        if ($type === 'near-me' && $cookieCity === '') {
            $paginator = new LengthAwarePaginator([], 0, $perPage, $page);
        } else {
            $queryType = $type === 'near-me' ? null : $type;
            $cityOverride = $type === 'near-me' ? $cookieCity : null;
            $paginator = $this->queryProducts($queryType, null, $request, $cityOverride)->paginate($perPage, ['*'], 'page', $page);

            if ($type !== 'near-me' && $paginator->total() === 0 && $cookieCity !== '') {
                // If city filter zeroed results, try without city
                $paginator = $this->queryProducts($queryType, null, $request, '')->paginate($perPage, ['*'], 'page', $page);
            } elseif ($type === 'near-me' && $cookieCity !== '' && $paginator->total() === 0) {
                // Fall back to a broader mix so the page still renders helpful content
                $paginator = $this->queryProducts(null, null, $request, '')->paginate($perPage, ['*'], 'page', $page);
            }
        }
        // Map products into view model while preserving paginator meta
        $mapped = $paginator->getCollection()->map(fn($p) => $this->transformProduct($p));
        $paginator->setCollection($mapped);

        if ($type === 'near-me') {
            $categories = collect();
        }

        return Inertia::render('Landing/Hub', [
            'type' => $type,
            'categories' => $categories,
            'products' => $paginator,
            'mapsKey' => env('GOOGLE_MAPS_API_KEY'),
            'geoStatus' => $geoStatus,
            'userCity' => $cookieCity,
        ]);
    }

    public function category(Request $request, string $type, string $category)
    {
        $type = strtolower($type);
        if (!in_array($type, self::TYPES, true)) {
            abort(404);
        }

        $cat = $this->findCategoryBySlug($category);
        if (!$cat) {
            // Fallback: virtual category by slug terms (noindex is handled in page head via canonical elsewhere)
            return $this->renderVirtualCategory($request, $type, $category, null);
        }

        $perPage = (int) $request->integer('per_page', 48);
        $perPage = max(6, min($perPage, 120));
        $page = (int) $request->integer('page', 1);
        $cookieCity = trim((string) $request->cookie('wow_city', ''));
        $paginator = $this->queryProducts($type, $cat->id, $request)->paginate($perPage, ['*'], 'page', $page);
        if ($paginator->total() === 0 && $cookieCity !== '') {
            $paginator = $this->queryProducts($type, $cat->id, $request, '')->paginate($perPage, ['*'], 'page', $page);
        }
        $paginator->setCollection($paginator->getCollection()->map(fn($p) => $this->transformProduct($p)));

        return Inertia::render('Landing/Listing', [
            'type' => $type,
            'category' => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $this->slugify($cat->name),
            ],
            'products' => $paginator,
            'mapsKey' => env('GOOGLE_MAPS_API_KEY'),
        ]);
    }

    public function city(Request $request, string $city)
    {
        $city = trim($city);
        // lightweight showcase across types for a city name (string match on locations)
        $products = $this->queryProducts(null, null, $request, $city)
            ->limit(24)
            ->get()
            ->map(fn($p) => $this->transformProduct($p));

        return Inertia::render('Landing/City', [
            'city' => $city,
            'products' => $products,
        ]);
    }

    public function cityCategory(Request $request, string $city, string $type, string $category)
    {
        $type = strtolower($type);
        if (!in_array($type, self::TYPES, true)) {
            abort(404);
        }
        $cat = $this->findCategoryBySlug($category);
        if (!$cat) {
            return $this->renderVirtualCategory($request, $type, $category, $city);
        }

        $perPage = (int) $request->integer('per_page', 48);
        $perPage = max(6, min($perPage, 120));
        $page = (int) $request->integer('page', 1);
        $paginator = $this->queryProducts($type, $cat->id, $request, $city)
            ->paginate($perPage, ['*'], 'page', $page);
        $paginator->setCollection($paginator->getCollection()->map(fn($p) => $this->transformProduct($p)));

        return Inertia::render('Landing/Listing', [
            'city' => $city,
            'type' => $type,
            'category' => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $this->slugify($cat->name),
            ],
            'products' => $paginator,
            'mapsKey' => env('GOOGLE_MAPS_API_KEY'),
        ]);
    }

    private function renderVirtualCategory(Request $request, string $type, string $slug, ?string $city)
    {
        $limit = (int) $request->integer('limit', 50);
        $terms = $this->synonymsForSlug($slug, $type);

        $q = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->with(['media','options.values','category']);

        // Type narrowing
        $this->applyTypeFilter($q, $type);

        // Mode filter (same rules as queryProducts)
        $modeParam = strtolower((string) $request->string('mode'));
        $cookieMode = strtolower((string) $request->cookie('wow_mode', ''));
        $mode = in_array($modeParam, ['online','in-person','all'], true) ? $modeParam : (in_array($cookieMode, ['online','in-person','all'], true) ? $cookieMode : '');
        if ($mode !== '') {
            $q->whereHas('options', function ($oq) use ($mode) {
                $oq->where(function($w){
                        $w->whereRaw("LOWER(TRIM(COALESCE(meta_name,''))) = 'locations'")
                          ->orWhereRaw("LOWER(TRIM(COALESCE(name,''))) IN ('location(s)','locations')");
                    });
                if ($mode === 'online') {
                    $oq->whereHas('values', function ($vq) { $vq->whereRaw("LOWER(TRIM(COALESCE(value,''))) = 'online'"); });
                } elseif ($mode === 'in-person') {
                    $oq->whereHas('values', function ($vq) {
                        $vq->whereRaw("LOWER(TRIM(COALESCE(value,''))) <> 'online'")
                           ->whereRaw("TRIM(COALESCE(value,'')) <> ''");
                    });
                }
            });
        }

        // Case-insensitive text relevance across key fields
        $q->where(function($qq) use ($terms){
            foreach ($terms as $t) {
                $like = '%'.strtolower($t).'%';
                $qq->orWhereRaw('LOWER(title) LIKE ?', [$like])
                   ->orWhereRaw('LOWER(summary) LIKE ?', [$like])
                   ->orWhereRaw('LOWER(body_html) LIKE ?', [$like])
                   ->orWhereRaw('LOWER(what_to_expect) LIKE ?', [$like])
                   ->orWhereRaw('LOWER(included) LIKE ?', [$like])
                   ->orWhereRaw('LOWER(COALESCE(tags_list,\'\')) LIKE ?', [$like]);
            }
        });

        // City scoping where provided
        if ($city !== null && trim($city) !== '') {
            $like = '%'.trim($city).'%';
            $q->whereHas('options', function($oq) use ($like){
                $oq->where('meta_name', 'locations')
                   ->whereHas('values', fn($vq)=>$vq->where('value','like',$like));
            });
        }

        // Popular first
        $q->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
          ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
          ->orderByRaw('COALESCE(reviews_count, 0) DESC');

        $items = $q->limit($limit)->get();
        $products = $items->map(fn($p) => $this->transformProduct($p));

        // Broaden if empty: include any type while keeping text relevance
        if ($products->isEmpty()) {
            $q2 = Product::query()
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->with(['media','options.values','category']);
            $q2->where(function($qq) use ($terms){
                foreach ($terms as $t) {
                    $like = '%'.strtolower($t).'%';
                    $qq->orWhereRaw('LOWER(title) LIKE ?', [$like])
                       ->orWhereRaw('LOWER(summary) LIKE ?', [$like])
                       ->orWhereRaw('LOWER(body_html) LIKE ?', [$like])
                       ->orWhereRaw('LOWER(what_to_expect) LIKE ?', [$like])
                       ->orWhereRaw('LOWER(included) LIKE ?', [$like])
                       ->orWhereRaw('LOWER(COALESCE(tags_list,\'\')) LIKE ?', [$like]);
                }
            });
            $q2->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
               ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
               ->orderByRaw('COALESCE(reviews_count, 0) DESC');
            $items = $q2->limit($limit)->get();
            $products = $items->map(fn($p) => $this->transformProduct($p));
        }

        $name = ucwords(str_replace('-', ' ', strtolower($slug)));

        return Inertia::render('Landing/Listing', [
            'city' => $city,
            'type' => $type,
            'category' => [ 'id' => null, 'name' => $name, 'slug' => strtolower($slug) ],
            'products' => $products,
        ]);
    }

    private function synonymsForSlug(string $slug, string $type): array
    {
        $s = strtolower($slug);
        $map = [
            // Therapies
            'massage-therapy' => ['massage','deep tissue','sports massage','swedish massage'],
            'manual-lymphatic-drainage' => ['manual lymphatic drainage','mld','lymphatic drainage'],
            'reiki' => ['reiki'],
            'acupuncture' => ['acupuncture'],
            'reflexology' => ['reflexology','foot reflexology'],
            'sound-therapy' => ['sound therapy','sound healing','sound bath','gong bath'],
            // Events
            'sound-bath' => ['sound bath','gong bath','sound healing'],
            'gong-bath' => ['gong bath','sound bath'],
            'breathwork' => ['breathwork','breathing','pranayama'],
            'meditation' => ['meditation','mindfulness'],
            'reiki-circles' => ['reiki circle','reiki share'],
            'ice-bath-workshops' => ['ice bath','cold immersion','cold plunge'],
            // Classes
            'yoga' => ['yoga','vinyasa','yin','hatha'],
            'qigong' => ['qigong','chi kung'],
            'tre' => ['tre','tension release exercises','trauma release exercises'],
            'pilates' => ['pilates'],
            // Gifts
            'massage-gift-voucher' => ['massage','gift'],
            'reiki-gift-voucher' => ['reiki','gift'],
            'sound-bath-gift-voucher' => ['sound bath','gift'],
        ];
        if (isset($map[$s])) return $map[$s];
        // Fallback to slug tokens
        $tokens = array_filter(explode('-', preg_replace('~[^a-z0-9\-]+~','-', $s)));
        if (empty($tokens)) return [$s];
        return [str_replace('-', ' ', $s), ...$tokens];
    }

    public function need(Request $request, string $need)
    {
        $slug = strtolower(trim($need));
        $map = [
            'sleep' => ['name' => 'Sleep better', 'terms' => ['sleep','insomnia','rest','nidra']],
            'stress' => ['name' => 'Stress reset', 'terms' => ['stress relief','calm','relaxation','breathwork','anxiety']],
            'energy' => ['name' => 'Energy boost', 'terms' => ['energy','focus','breath','mobility','sauna','cold']],
            'pain' => ['name' => 'Pain relief', 'terms' => ['pain relief','mobility','massage','acupuncture','physio']],
        ];
        // alias support
        if (!isset($map[$slug])) {
            if (in_array($slug, ['sleep-better'])) $slug = 'sleep';
            elseif (in_array($slug, ['stress-relief','calm'])) $slug = 'stress';
            elseif (in_array($slug, ['energy-boost'])) $slug = 'energy';
            elseif (in_array($slug, ['pain-relief'])) $slug = 'pain';
        }
        // Add support for additional recognised needs without 404
        if (!isset($map[$slug])) {
            // Minimal curated additions
            if ($slug === 'gut') {
                $map['gut'] = [
                    'name' => 'Gut health',
                    'terms' => ['gut','digestion','digestive','microbiome','bloating','stomach','ibs']
                ];
            }
        }
        // Generic fallback: treat any slug as a free-text need page
        if (!isset($map[$slug])) {
            $readable = ucwords(str_replace('-', ' ', $slug));
            $tokens = array_filter(explode('-', $slug));
            $terms = array_values(array_unique(array_filter(array_merge([$slug, strtolower($readable)], $tokens))));
            $map[$slug] = [ 'name' => $readable, 'terms' => $terms ];
        }

        $conf = $map[$slug];
        $terms = $conf['terms'];

        $q = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->with(['media','options.values','category']);

        $q->where(function($qq) use ($terms){
            foreach ($terms as $t) {
                $like = '%'.$t.'%';
                $qq->orWhere('title','like',$like)
                   ->orWhere('summary','like',$like)
                   ->orWhere('body_html','like',$like)
                   ->orWhere('what_to_expect','like',$like)
                   ->orWhere('included','like',$like)
                   ->orWhere('tags_list','like',$like);
            }
        });

        // Prefer popular within matches using weighted favorability
        $q->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
          ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
          ->orderByRaw('COALESCE(reviews_count, 0) DESC');

        $products = $q->limit(48)->get()->map(fn($p) => $this->transformProduct($p));

        return Inertia::render('Landing/Need', [
            'need' => [ 'slug' => $slug, 'name' => $conf['name'] ],
            'products' => $products,
        ]);
    }

    public function plan(Request $request)
    {
        $what = trim((string) $request->string('what'));
        $type = trim((string) $request->string('type'));
        $mode = strtolower((string) $request->string('mode'));
        $where = trim((string) $request->string('where'));
        $priceMax = $request->filled('price_max') ? (float) $request->input('price_max') : null;
        $when = strtolower((string) $request->string('when'));

        $q = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->with(['media','options.values','category']);

        // Text search across relevant fields
        if ($what !== '') {
            $q->where(function ($qq) use ($what) {
                $pattern = '%'.$what.'%';
                $qq->where('title','like',$pattern)
                   ->orWhere('summary','like',$pattern)
                   ->orWhere('body_html','like',$pattern)
                   ->orWhere('what_to_expect','like',$pattern)
                   ->orWhere('included','like',$pattern)
                   ->orWhere('tags_list','like',$pattern);
            });
        }

        // Type filter
        if ($type !== '') {
            $this->applyTypeFilter($q, $type);
        }

        // Mode filter based on locations option
        if (in_array($mode, ['online','in-person'], true)) {
            $q->whereHas('options', function ($oq) use ($mode) {
                $oq->where('meta_name', 'locations')
                   ->whereHas('values', function ($vq) use ($mode) {
                       if ($mode === 'online') {
                           $vq->where('value', 'Online');
                       } else {
                           $vq->where('value', '!=', 'Online');
                       }
                   });
            });
        }

        // Where (city or area) matches options->values
        if ($where !== '') {
            $like = '%'.$where.'%';
            $q->whereHas('options', function ($oq) use ($like) {
                $oq->where('meta_name','locations')
                   ->whereHas('values', function ($vq) use ($like) {
                       $vq->where('value', 'like', $like);
                   });
            });
        }

        // Price
        if ($priceMax !== null) {
            $pm = $priceMax;
            $q->where(function($qq) use ($pm) {
                $qq->where('price','<=',$pm)
                   ->orWhere('price','<=',$pm*100);
            });
        }

        // When window (best-effort; only applies when date fields exist)
        $now = now();
        if ($when === 'this week') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
            $q->where(function($qq) use ($start,$end){
                $qq->whereBetween('meta_json->date', [$start->toDateString(), $end->toDateString()])
                   ->orWhere(function($q2) use ($start,$end){
                       $q2->where('meta_json->start_date','<=',$end->toDateString())
                          ->where('meta_json->end_date','>=',$start->toDateString());
                   });
            });
        } elseif ($when === 'this weekend') {
            $sat = $now->copy()->startOfWeek()->addDays(5); // Saturday
            $sun = $now->copy()->startOfWeek()->addDays(6)->endOfDay();
            $q->where(function($qq) use ($sat,$sun){
                $qq->whereBetween('meta_json->date', [$sat->toDateString(), $sun->toDateString()])
                   ->orWhere(function($q2) use ($sat,$sun){
                       $q2->where('meta_json->start_date','<=',$sun->toDateString())
                          ->where('meta_json->end_date','>=',$sat->toDateString());
                   });
            });
        } elseif ($when === 'next month') {
            $first = $now->copy()->addMonthNoOverflow()->startOfMonth();
            $last = $first->copy()->endOfMonth();
            $q->where(function($qq) use ($first,$last){
                $qq->whereBetween('meta_json->date', [$first->toDateString(), $last->toDateString()])
                   ->orWhere(function($q2) use ($first,$last){
                       $q2->where('meta_json->start_date','<=',$last->toDateString())
                          ->where('meta_json->end_date','>=',$first->toDateString());
                   });
            });
        }

        // Sort by weighted favorability within the filters
        $q->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
          ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
          ->orderByRaw('COALESCE(reviews_count, 0) DESC');

        $perPage = (int) $request->integer('per_page', 48);
        $perPage = max(6, min($perPage, 120));
        $page = (int) $request->integer('page', 1);
        $paginator = $q->paginate($perPage, ['*'], 'page', $page);
        $paginator->setCollection($paginator->getCollection()->map(fn($p) => $this->transformProduct($p)));

        $fallback = [];
        if ($paginator->total() === 0) {
            $fb = Product::query()
                ->withCount('reviews')
                ->withAvg('reviews','rating')
                ->with(['media','options.values','category']);
            // keep mode and where if given
            if (in_array($mode, ['online','in-person'], true)) {
                $fb->whereHas('options', function ($oq) use ($mode) {
                    $oq->where('meta_name','locations')
                       ->whereHas('values', function ($vq) use ($mode) {
                           if ($mode === 'online') $vq->where('value','Online'); else $vq->where('value','!=','Online');
                       });
                });
            }
            if ($where !== '') {
                $like = '%'.$where.'%';
                $fb->whereHas('options', function ($oq) use ($like) {
                    $oq->where('meta_name','locations')
                       ->whereHas('values', fn($vq)=>$vq->where('value','like',$like));
                });
            }
            $fb->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
               ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
               ->orderByRaw('COALESCE(reviews_count, 0) DESC');
            $fallback = $fb->limit(24)->get()->map(fn($p) => $this->transformProduct($p))->values();
        }

        return Inertia::render('Landing/Plan', [
            'answers' => [
                'what' => $what,
                'type' => $type,
                'mode' => $mode,
                'where' => $where,
                'price_max' => $priceMax,
                'when' => $when,
            ],
            'products' => $paginator,
            'fallback' => $fallback,
        ]);
    }

    private function findCategoryBySlug(string $slug): ?ProductCategory
    {
        $slug = strtolower($slug);
        // Attempt to match by slugified name
        $cats = ProductCategory::query()->get();
        foreach ($cats as $c) {
            if ($this->slugify($c->name) === $slug) return $c;
        }
        return null;
    }

    private function slugify(?string $name): string
    {
        $s = strtolower(trim((string)$name));
        $s = preg_replace('~[^a-z0-9]+~', '-', $s ?? '') ?? '';
        return trim($s, '-');
    }

    private function applyTypeFilter($query, ?string $type): void
    {
        if (!$type) return;
        $lc = strtolower($type);
        if ($lc === 'events') {
            $query->where(function($q){
                $q->whereRaw("LOWER(product_type) like '%event%'")
                  ->orWhereRaw("LOWER(product_type) like '%workshop%'")
                  ->orWhereNotNull('meta_json->date')
                  ->orWhereNotNull('meta_json->start_date');
            });
        } elseif ($lc === 'workshops') {
            $query->whereRaw("LOWER(product_type) like '%workshop%'");
        } elseif ($lc === 'classes') {
            $query->whereRaw("LOWER(product_type) like '%class%'");
        } elseif ($lc === 'therapies') {
            $query->where(function($q){
                $q->whereRaw("LOWER(COALESCE(product_type,'')) not like '%event%'")
                  ->whereRaw("LOWER(COALESCE(product_type,'')) not like '%workshop%'")
                  ->whereRaw("LOWER(COALESCE(product_type,'')) not like '%class%'");
            });
        } elseif ($lc === 'retreats') {
            $query->whereRaw("LOWER(product_type) like '%retreat%'");
        } elseif ($lc === 'gifts') {
            $query->where(function($q){
                $q->whereRaw("LOWER(COALESCE(tags_list,'')) like '%gift%'")
                  ->orWhereRaw("LOWER(COALESCE(product_type,'')) like '%gift%'");
            });
        }
    }

    private function queryProducts(?string $type, ?int $categoryId, Request $request, ?string $city = null)
    {
        $q = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->with(['media', 'options.values', 'category']);

        if ($categoryId) {
            $q->where('category_id', $categoryId);
        }
        if ($type) {
            $this->applyTypeFilter($q, $type);
        }
        // Apply city filter either from explicit param or from cookies (wow_city)
        $cookieCity = trim((string) $request->cookie('wow_city', ''));
        // If $city is provided (even empty string), use it; else fall back to cookie
        $useCity = ($city !== null) ? trim((string)$city) : ($cookieCity ?: null);
        if ($useCity) {
            $like = '%'.$useCity.'%';
            $q->whereHas('options', function($oq) use ($like){
                $oq->where('meta_name', 'locations')
                   ->whereHas('values', function($vq) use ($like){
                       $vq->where('value', 'like', $like);
                   });
            });
        }

        // Mode filter: query param takes precedence over cookie; accept alias `format`
        $modeParam = strtolower((string) ($request->filled('mode') ? $request->string('mode') : $request->string('format')));
        $cookieMode = strtolower((string) $request->cookie('wow_mode', ''));
        $mode = in_array($modeParam, ['online','in-person','all'], true) ? $modeParam : (in_array($cookieMode, ['online','in-person','all'], true) ? $cookieMode : '');
        if ($mode !== '') {
            $q->whereHas('options', function ($oq) use ($mode) {
                // Target the Locations option only (case-insensitive exact names)
                $oq->where(function($w){
                        $w->whereRaw("LOWER(TRIM(COALESCE(meta_name,''))) = 'locations'")
                          ->orWhereRaw("LOWER(TRIM(COALESCE(name,''))) IN ('location(s)','locations')");
                    });
                if ($mode === 'online') {
                    // EXACT value 'Online' (case-insensitive). Do not match partials like 'online (live)'.
                    $oq->whereHas('values', function ($vq) {
                        $vq->whereRaw("LOWER(TRIM(COALESCE(value,''))) = 'online'");
                    });
                } elseif ($mode === 'in-person') {
                    // Any non-empty value that is NOT exactly 'Online'
                    $oq->whereHas('values', function ($vq) {
                        $vq->whereRaw("LOWER(TRIM(COALESCE(value,''))) <> 'online'")
                           ->whereRaw("TRIM(COALESCE(value,'')) <> ''");
                    });
                } else {
                    // 'all': just ensure Locations exists (handled above)
                }
            });
        }

        // Anytime (on-demand): no scheduled date/time in meta
        if ($request->boolean('anytime')) {
            $q->whereRaw("(JSON_EXTRACT(meta_json, '$.date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.date')) = '')")
              ->whereRaw("(JSON_EXTRACT(meta_json, '$.start_date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.start_date')) = '')")
              ->whereRaw("(JSON_EXTRACT(meta_json, '$.end_date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.end_date')) = '')");
        }

        // Sorting preference: popular by default
        $sort = strtolower($request->string('sort','popular')->toString());
        if ($sort === 'newest') {
            $q->latest('id');
        } elseif ($sort === 'price_asc') {
            $q->orderBy('price','asc');
        } elseif ($sort === 'price_desc') {
            $q->orderBy('price','desc');
        } else {
            $q->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
              ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
              ->orderByRaw('COALESCE(reviews_count, 0) DESC');
        }

        return $q;
    }

    private function transformProduct(Product $p): array
    {
        $locations = method_exists($p, 'getLocations') ? $p->getLocations() : [];
        $isOnline = in_array('Online', $locations, true);
        $physical = array_values(array_filter($locations, fn($l)=> $l !== 'Online'));
        $meta = $p->meta_json ?? [];
        $typeSeg = $this->typeSegment($p);
        $slug = $this->slugify($p->title ?? (string)$p->id);
        return [
            'id' => $p->id,
            'title' => $p->title,
            'type' => $p->product_type ?: 'experience',
            'category' => $p->category ? ['id'=>$p->category->id,'name'=>$p->category->name] : null,
            'mode' => $isOnline && count($physical) === 0 ? 'Online' : (count($physical) ? 'In-person' : null),
            'location' => $physical[0] ?? ($isOnline ? 'Online' : null),
            'locations' => $locations,
            'price' => $p->price ?? null,
            'compare_at_price' => $meta['compare_at_price'] ?? null,
            'currency' => $meta['currency'] ?? 'GBP',
            'rating' => round((float)($p->reviews_avg_rating ?? 0), 1) ?: null,
            'review_count' => (int)($p->reviews_count ?? 0),
            'image' => method_exists($p, 'getFirstImageUrl') ? $p->getFirstImageUrl() : null,
            'tags' => $p->tags_list ? array_map('trim', explode(',', $p->tags_list)) : [],
            'url' => url('/'.$typeSeg.'/' . $p->id . '-' . $slug),
        ];
    }

    private function typeSegment(Product $p): string
    {
        $t = strtolower((string) $p->product_type);
        $tags = strtolower((string) $p->tags_list);
        if (str_contains($t, 'workshop')) return 'workshops';
        if (str_contains($t, 'event')) return 'events';
        if (str_contains($t, 'class')) return 'classes';
        if (str_contains($t, 'retreat')) return 'retreats';
        if (str_contains($t, 'gift') || str_contains($tags, 'gift')) return 'gifts';
        return 'therapies';
    }

    public function offering(\Illuminate\Http\Request $request, string $type, string $handle)
    {
        $type = strtolower($type);
        if (!in_array($type, self::TYPES, true)) abort(404);

        // Accept forms: "{id}-{slug}", "{id}", or legacy "{handle}"
        $id = null;
        if (preg_match('/^(\d+)(?:-.+)?$/', (string)$handle, $m)) {
            $id = (int) $m[1];
        }

        $query = Product::query()
            ->with(['media','options.values','variants','reviews.user','category','vendor'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');
        if ($id) {
            $query->where('id', $id);
        } else {
            $query->where('handle', $handle);
        }
        $product = $query->first();
        if (!$product) abort(404);

        $meta = $product->meta_json ?? [];
        $locations = $product->getLocations();
        $isOnline = in_array('Online', $locations, true);
        $phys = array_values(array_filter($locations, fn($l) => $l !== 'Online'));
        $images = $product->media->map(function($m){
            $url = (string) ($m->media_url ?? '');
            if ($url === '') return null;
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) return $url;
            $backend = rtrim((string) env('BACKEND_ASSET_URL', env('BACKEND_URL', '')), '/');
            $clean = ltrim($url, '/');
            return $backend ? $backend . '/storage/' . $clean : asset('storage/' . $clean);
        })->filter()->values();

        // Eager-load options + values + variants for the buy box
        $product->loadMissing(['options.values', 'variants']);

        // Build options array and a lookup of value id -> label for variant option_ids mapping
        $valueLookup = [];
        $optionsArr = $product->options->map(function($o) use (&$valueLookup){
            $vals = $o->values->pluck('value', 'id')->all();
            foreach ($vals as $id => $label) { $valueLookup[$id] = $label; }
            return [
                'name' => $o->name ?? $o->meta_name,
                'meta_name' => $o->meta_name,
                'values' => array_values($vals),
            ];
        })->values()->all();

        $variantsArr = $product->variants->map(function($v) use ($valueLookup){
            // Prefer explicit options array; else derive from option_ids
            $optList = [];
            if (is_array($v->options) && count($v->options)) {
                $optList = array_values($v->options);
            } elseif (is_string($v->options) && !empty($v->options)) {
                // Some variants store options as a JSON string like {"option1":"1","option2":"3","option3":"Online"}
                $decoded = json_decode($v->options, true);
                if (is_array($decoded) && !empty($decoded)) {
                    // Keep natural option ordering by key name if present
                    // Accept option1, option2, option3... else fallback to array_values
                    $ordered = [];
                    foreach (['option1','option2','option3','option4','option5'] as $k) {
                        if (array_key_exists($k, $decoded)) { $ordered[] = $decoded[$k]; unset($decoded[$k]); }
                    }
                    $optList = array_values(array_merge($ordered, $decoded));
                }
            } elseif (!empty($v->option_ids)) {
                $ids = json_decode($v->option_ids, true) ?: [];
                foreach ($ids as $oid) {
                    if (isset($valueLookup[$oid])) { $optList[] = $valueLookup[$oid]; }
                }
            }
            return [
                'id' => $v->id,
                'options' => $optList,
                'price' => $v->price, // pounds or pence; frontend normalizes
                'compare' => $v->metadata['compare_at_price'] ?? null,
                'available' => ($v->inventory_quantity ?? 0) > 0 || is_null($v->inventory_quantity),
            ];
        })->values()->all();

        // Fallback: if variants have no option labels but there is a Sessions option, infer mapping by price order vs session duration order
        try {
            $allEmpty = true;
            foreach ($variantsArr as $vv) { if (!empty($vv['options'])) { $allEmpty = false; break; } }
            if ($allEmpty && is_array($optionsArr) && count($optionsArr)) {
                // Attempt a full cartesian combination from option values first
                $combos = [[]];
                foreach ($optionsArr as $idx => $opt) {
                    $vals = $opt['values'] ?? [];
                    if (empty($vals)) {
                        foreach ($combos as &$combo) { $combo[$idx] = ''; }
                        unset($combo);
                        continue;
                    }
                    $expanded = [];
                    foreach ($combos as $combo) {
                        foreach ($vals as $val) {
                            $next = $combo;
                            $next[$idx] = (string)$val;
                            $expanded[] = $next;
                        }
                    }
                    $combos = $expanded;
                    if (count($combos) > 300) { $combos = []; break; }
                }

                if (count($combos) > 0 && count($combos) === count($variantsArr)) {
                    $optionCount = count($optionsArr);
                    foreach ($variantsArr as $idx => $variantData) {
                        $combo = $combos[$idx] ?? [];
                        $normalized = [];
                        for ($oi = 0; $oi < $optionCount; $oi++) {
                            $normalized[$oi] = (string) ($combo[$oi] ?? ($optionsArr[$oi]['values'][0] ?? ''));
                        }
                        $variantsArr[$idx]['options'] = $normalized;
                    }
                } else {
                    $peopleIdx = null; $sessionsIdx = null; $locIdx = null;
                    foreach ($optionsArr as $i => $opt) {
                        $nm = strtolower(trim((string)($opt['meta_name'] ?? $opt['name'] ?? '')));
                        if ($peopleIdx === null && str_contains($nm, 'person')) $peopleIdx = $i;
                        if ($sessionsIdx === null && str_contains($nm, 'session')) $sessionsIdx = $i;
                        if ($locIdx === null && str_contains($nm, 'location')) $locIdx = $i;
                    }
                    if ($sessionsIdx !== null) {
                        $sessionVals = $optionsArr[$sessionsIdx]['values'] ?? [];
                        // Parse labels to a numeric score in minutes
                        $score = function($label) {
                            $s = strtolower(trim((string)$label));
                            if (preg_match('/(\d+(?:\.\d+)?)\s*(hour|hr|hours|hrs)/', $s, $m)) { return (float)$m[1] * 60; }
                            if (preg_match('/(\d+(?:\.\d+)?)\s*(min|mins|minute|minutes)/', $s, $m)) { return (float)$m[1]; }
                            if (preg_match('/\d+/', $s, $m)) { return (float)$m[0]; }
                            return 0.0;
                        };
                        $pairs = [];
                        foreach ($sessionVals as $lbl) { $pairs[] = ['label' => $lbl, 'n' => $score($lbl)]; }
                        usort($pairs, function($a, $b){ return $a['n'] <=> $b['n']; });
                        $sortedSession = array_map(fn($p) => $p['label'], $pairs);

                        // Sort variants by numeric price ascending
                        $sortedVariants = $variantsArr;
                        usort($sortedVariants, function($a, $b){ return (float)$a['price'] <=> (float)$b['price']; });

                        // Prepare default labels for people and location (first values if available)
                        $peopleLabel = ($peopleIdx !== null && isset($optionsArr[$peopleIdx]['values'][0])) ? (string)$optionsArr[$peopleIdx]['values'][0] : '';
                        $locLabel = ($locIdx !== null && isset($optionsArr[$locIdx]['values'][0])) ? (string)$optionsArr[$locIdx]['values'][0] : '';
                        $optCount = count($optionsArr);

                        // Map by index
                        $mapped = [];
                        foreach ($sortedVariants as $i => $sv) {
                            $sessionLabel = (string)($sortedSession[$i] ?? ($sessionVals[$i] ?? ''));
                            $optList = array_fill(0, $optCount, '');
                            if ($peopleIdx !== null) $optList[$peopleIdx] = $peopleLabel;
                            if ($sessionsIdx !== null) $optList[$sessionsIdx] = $sessionLabel;
                            if ($locIdx !== null) $optList[$locIdx] = $locLabel;
                            $sv['options'] = $optList;
                            $mapped[$sv['id']] = $sv;
                        }
                        // Rebuild in original order with mapped options
                        foreach ($variantsArr as $k => $vv) { if (isset($mapped[$vv['id']])) { $variantsArr[$k] = $mapped[$vv['id']]; } }
                    }
                }
            }
        } catch (\Throwable $e) { /* swallow fallback errors */ }

        $vendor = $product->vendor;
        $clientReviews = [];
        $vendorReviewCount = 0;
        $vendorRatingAvg = null;
        if ($vendor) {
            $vendorReviewBase = Review::query()
                ->where('vendor_id', $vendor->id)
                ->whereRaw("TRIM(COALESCE(review_text, '')) <> ''");

            $vendorReviewCount = (int) (clone $vendorReviewBase)->count();
            if ($vendorReviewCount > 0) {
                $vendorRatingAvg = round((float) ((clone $vendorReviewBase)->avg('rating') ?? 0), 1);
            }

            $clientReviews = (clone $vendorReviewBase)
                ->with('user')
                ->latest('created_at')
                ->take(6)
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => (int) ($review->rating ?? 0),
                        'body' => trim((string) $review->review_text),
                        'author' => optional($review->user)->name ?? 'Verified client',
                        'date' => optional($review->created_at)->format('M Y') ?? '',
                    ];
                })->values()->all();
        }
        $ratingFallback = round((float)($product->reviews_avg_rating ?? 0), 1) ?: null;
        $countFallback = (int)($product->reviews_count ?? 0);
        if ($vendorReviewCount > 0 && $vendorRatingAvg !== null) {
            $ratingFallback = $vendorRatingAvg;
            $countFallback = $vendorReviewCount;
        }
        $metaSafety = $meta['safety_notes'] ?? ($meta['safety'] ?? '');
        $metaContra = $meta['contraindications'] ?? '';
        $metaBenefits = $meta['benefits'] ?? [];
        $metaWhoFor = $meta['who_for'] ?? [];
        $metaWhoNot = $meta['who_not_for'] ?? [];
        $metaFaq = $meta['faq'] ?? [];
        $metaAftercare = $meta['aftercare'] ?? ($meta['after_care'] ?? '');

        $data = [
            'id' => $product->id,
            'title' => $product->title,
            'type' => $product->product_type ?: 'experience',
            'category' => $product->category ? ['id'=>$product->category->id,'name'=>$product->category->name] : null,
            'rating' => $ratingFallback,
            'review_count' => $countFallback,
            'vendor_rating' => $vendorRatingAvg,
            'vendor_review_count' => $vendorReviewCount,
            'price' => $product->price ?? null,
            'price_min' => $product->variants_min_price ?? ($product->price ?? null),
            'price_max' => $product->variants_max_price ?? ($product->price ?? null),
            'compare_at_price' => $meta['compare_at_price'] ?? null,
            'currency' => $meta['currency'] ?? 'GBP',
            'image' => $product->getFirstImageUrl(),
            'images' => $images,
            'options' => $optionsArr,
            'variants' => $variantsArr,
            'mode' => $isOnline && count($phys) === 0 ? 'Online' : (count($phys) ? 'In-person' : null),
            'location' => $phys[0] ?? ($isOnline ? 'Online' : null),
            'locations' => $locations,
            'description' => (string) ($product->description ?? ''),
            'summary' => (string) ($product->summary ?? ''),
            'body_html' => (string) ($product->body_html ?? ''),
            'what_to_expect' => (string) ($product->what_to_expect ?? ''),
            'included' => (string) ($product->included ?? ''),
            'aftercare' => (string) $metaAftercare,
            'duration' => $meta['duration'] ?? $meta['duration_minutes'] ?? null,
            'tags' => $product->tags_list ? array_values(array_filter(array_map('trim', explode(',', $product->tags_list)))) : [],
            'benefits' => is_array($metaBenefits) ? array_values(array_filter($metaBenefits)) : [],
            'who_for' => is_array($metaWhoFor) ? array_values(array_filter($metaWhoFor)) : [],
            'who_not_for' => is_array($metaWhoNot) ? array_values(array_filter($metaWhoNot)) : [],
            'faq' => is_array($metaFaq) ? array_values(array_filter($metaFaq)) : [],
            'safety_notes' => (string) $metaSafety,
            'contraindications' => (string) $metaContra,
            'date' => $meta['date'] ?? null,
            'start_date' => $meta['start_date'] ?? null,
            'end_date' => $meta['end_date'] ?? null,
            'practitioner' => $vendor ? [
                'name' => $vendor->name ?? '',
                'pronouns' => $vendor->pronouns ?? null,
                'bio' => $vendor->bio ?? $vendor->about ?? '',
                'credentials' => $vendor->credentials ?? $vendor->qualifications ?? '',
                'photo' => $vendor->photo_url ?? $vendor->headshot_url ?? $vendor->avatar_url ?? null,
                'location' => $vendor->location ?? '',
                'specialties' => is_array($vendor->specialties ?? null) ? array_values(array_filter($vendor->specialties)) : [],
            ] : null,
            'reviews' => $product->reviews->map(function($r){
                return [
                    'id' => $r->id,
                    'rating' => (int)($r->rating ?? 0),
                    'review' => (string)($r->review ?? ''),
                    'user' => $r->user ? [
                        'id' => $r->user->id,
                        'name' => trim(($r->user->name ?? '') ?: ($r->user->email ?? 'User')),
                    ] : null,
                    'created_at' => optional($r->created_at)->toIso8601String(),
                ];
            })->values(),
            'client_reviews' => $clientReviews,
        ];

        return view('offering.show', [
            'type' => $type,
            'product' => $data,
        ]);
    }
}
