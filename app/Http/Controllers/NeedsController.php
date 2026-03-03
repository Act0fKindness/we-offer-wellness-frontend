<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NeedsController extends Controller
{
    public function index(Request $request)
    {
        $needs = $this->needsIndex();

        return view('needs.index', [
            'seo' => [
                'title' => 'Browse by Need | We Offer Wellness™',
                'description' => 'Find holistic therapies and experiences by what you need most, from sleep and stress support to pain relief and more.',
                'robots' => 'index,follow',
            ],
            'needs' => $needs,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $need = $this->findNeedBySlug($slug);
        abort_if($need === null, 404);

        $filters = [
            'q'        => $need['title'],
            'need'     => $need['key'],
            'format'   => (string) $request->query('format', ''),
            'location' => (string) $request->query('location', ''),
            'sort'     => (string) $request->query('sort', ''),
            'page'     => max(1, (int) $request->query('page', 1)),
            'per_page' => min(48, max(8, (int) $request->query('per_page', 24))),
        ];

        $results = $this->fetchOfferings($filters);

        // If the URL has facets/filters, avoid indexing those variants
        $hasFacets = (bool) (
            $filters['format'] ||
            $filters['location'] ||
            $filters['sort'] ||
            $request->has('page') ||
            $request->has('per_page')
        );

        return view('needs.show', [
            'seo' => [
                'title' => $need['seo_title'] ?? ($need['title'] . ' | We Offer Wellness™'),
                'description' => $need['seo_description'] ?? ('Browse experiences and therapies for ' . $need['title'] . '.'),
                'robots' => $hasFacets ? 'noindex,follow' : 'index,follow',
                'canonical' => url('/needs/' . $slug),
            ],
            'need' => $need,
            'filters' => $filters,
            'results' => $results,
        ]);
    }

    private function needsIndex(): array
    {
        return [
            [
                'key' => 'stress-anxiety',
                'slug' => 'stress-and-anxiety',
                'title' => 'Stress & Anxiety',
                'seo_title' => 'Stress & Anxiety Support | We Offer Wellness™',
                'seo_description' => 'Explore calming experiences and holistic therapies designed to support stress and anxiety.',
            ],
            [
                'key' => 'sleep-issues',
                'slug' => 'sleep-issues',
                'title' => 'Sleep Issues',
                'seo_title' => 'Sleep Support | We Offer Wellness™',
                'seo_description' => 'Wind down with therapies and experiences that support better sleep and rest.',
            ],
            [
                'key' => 'low-mood-burnout',
                'slug' => 'low-mood-burnout',
                'title' => 'Low Mood & Burnout',
                'seo_title' => 'Low Mood & Burnout Support | We Offer Wellness™',
                'seo_description' => 'Discover experiences focused on nervous system soothing, rest and recovery.',
            ],
            [
                'key' => 'overwhelm',
                'slug' => 'overwhelm',
                'title' => 'Overwhelm & Frazzled Feelings',
                'seo_title' => 'Overwhelm & Frazzled Feelings | We Offer Wellness™',
                'seo_description' => 'Find calm-first sessions for when life feels loud or overstimulating.',
            ],
            [
                'key' => 'worry',
                'slug' => 'worry',
                'title' => 'Worry & Racing Thoughts',
                'seo_title' => 'Worry & Racing Thoughts | We Offer Wellness™',
                'seo_description' => 'Supportive practices to soften spirals, release tension and create space.',
            ],
            [
                'key' => 'pain-management',
                'slug' => 'pain-management',
                'title' => 'Pain, Tension & Tightness',
                'seo_title' => 'Pain Management | We Offer Wellness™',
                'seo_description' => 'Discover supportive therapies focused on easing discomfort and improving wellbeing.',
            ],
            [
                'key' => 'mens-wellbeing',
                'slug' => 'mens-wellbeing',
                'title' => 'Men’s Wellbeing',
                'seo_title' => 'Men’s Wellbeing | We Offer Wellness™',
                'seo_description' => 'Explore men’s breathwork, bodywork and whole-health support.',
            ],
            [
                'key' => 'digestive-health',
                'slug' => 'digestive-health',
                'title' => 'Gut Health & Digestion',
                'seo_title' => 'Digestive Health | We Offer Wellness™',
                'seo_description' => 'Explore holistic approaches to digestion support, stress reduction and balance.',
            ],
            [
                'key' => 'fertility-pregnancy',
                'slug' => 'fertility-pregnancy',
                'title' => 'Fertility & Pregnancy Support',
                'seo_title' => 'Fertility & Pregnancy | We Offer Wellness™',
                'seo_description' => 'Gentle, expert-held experiences for every stage of the fertility and pregnancy journey.',
            ],
            [
                'key' => 'nervous-system',
                'slug' => 'nervous-system',
                'title' => 'Nervous System & Trauma Support',
                'seo_title' => 'Nervous System Support | We Offer Wellness™',
                'seo_description' => 'Somatic, breath and energy sessions designed to support regulation and repair.',
            ],
            [
                'key' => 'breathwork',
                'slug' => 'breathwork',
                'title' => 'Breathwork for Calm',
                'seo_title' => 'Breathwork for Calm | We Offer Wellness™',
                'seo_description' => 'Online and in-person breathwork journeys for release, clarity and grounded focus.',
            ],
            [
                'key' => 'guided-meditation',
                'slug' => 'guided-meditation',
                'title' => 'Guided Meditation & Sound',
                'seo_title' => 'Guided Meditation & Sound | We Offer Wellness™',
                'seo_description' => 'Sound baths, guided rests and meditations to soften the edges of your day.',
            ],
            [
                'key' => 'corporate-wellbeing',
                'slug' => 'corporate-wellbeing',
                'title' => 'Corporate Wellbeing Boosters',
                'seo_title' => 'Corporate Wellbeing | We Offer Wellness™',
                'seo_description' => 'Desk resets, on-site massage and sessions curated for modern teams.',
            ],
        ];
    }

    private function findNeedBySlug(string $slug): ?array
    {
        foreach ($this->needsIndex() as $need) {
            if (($need['slug'] ?? null) === $slug) {
                return $need;
            }
        }
        return null;
    }

    private function fetchOfferings(array $query): array
    {
        $cacheKey = 'needs:offerings:' . md5(json_encode($query));

        return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($query) {
            $needKey = $query['need'] ?? null;
            if (!$needKey) {
                return ['items' => [], 'meta' => []];
            }

            $perPage = (int)($query['per_page'] ?? 24);
            $page    = max(1, (int)($query['page'] ?? 1));

            $builder = Product::query()
                ->with(['media', 'category', 'options.values'])
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->withMin('variants', 'price')
                ->withMax('variants', 'price')
                ->whereJsonContains('by_need', $needKey)
                ->where(function ($q) {
                    $q->whereHas('status', function ($qs) {
                        $qs->whereIn('status', ['live', 'approved']);
                    })->orWhereNull('product_status_id');
                });

            $format = strtolower((string) ($query['format'] ?? ''));
            if ($format === 'online') {
                $builder->whereHas('options', function ($q) {
                    $q->where('meta_name', 'locations')
                        ->whereHas('values', function ($q2) {
                            $q2->whereRaw('LOWER(value) = ?', ['online']);
                        });
                });
            } elseif ($format === 'in_person') {
                $builder->whereHas('options', function ($q) {
                    $q->where('meta_name', 'locations')
                        ->whereHas('values', function ($q2) {
                            $q2->whereRaw('LOWER(value) != ?', ['online']);
                        });
                });
            }

            if ($location = trim((string) ($query['location'] ?? ''))) {
                $builder->whereHas('options', function ($q) use ($location) {
                    $q->where('meta_name', 'locations')
                        ->whereHas('values', function ($q2) use ($location) {
                            $q2->where('value', 'like', "%{$location}%");
                        });
                });
            }

            $sort = $query['sort'] ?? '';
            if ($sort === 'price_asc') {
                $builder->orderByRaw('COALESCE(variants_min_price, price) asc');
            } elseif ($sort === 'price_desc') {
                $builder->orderByRaw('COALESCE(variants_min_price, price) desc');
            } elseif ($sort === 'rating_desc') {
                $builder->orderByRaw('COALESCE(reviews_avg_rating, 0) desc nulls last');
            } else {
                $builder->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
                        ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
                        ->orderByRaw('COALESCE(reviews_count, 0) DESC');
            }

            $total = (clone $builder)->count();
            $items = $builder->forPage($page, $perPage)->get()->map(fn (Product $product) => $this->mapProductForNeed($product))->filter()->values()->all();

            $lastPage = max(1, (int) ceil($total / max(1, $perPage)));

            return [
                'items' => $items,
                'meta'  => [
                    'current_page' => $page,
                    'last_page'    => $lastPage,
                ],
            ];
        });
    }

    private function mapProductForNeed(Product $product): array
    {
        $image = $product->getFirstImageUrl();
        $rating = $product->reviews_avg_rating ?? null;
        $priceMin = $product->variants_min_price ?? $product->price;

        return [
            'id' => $product->id,
            'title' => $product->title,
            'type' => $product->product_type ?? 'Therapy',
            'image' => $image,
            'rating' => $rating ? round($rating, 1) : null,
            'review_count' => (int) ($product->reviews_count ?? 0),
            'price' => $product->price,
            'price_min' => $priceMin,
            'url' => $this->productUrl($product),
        ];
    }

    private function productUrl(Product $product): string
    {
        $t = strtolower((string) ($product->product_type ?? ''));
        $tags = strtolower((string) ($product->tags_list ?? ''));
        $seg = 'therapies';
        if (str_contains($t, 'workshop')) $seg = 'events';
        elseif (str_contains($t, 'event')) $seg = 'events';
        elseif (str_contains($t, 'class')) $seg = 'classes';
        elseif (str_contains($t, 'retreat')) $seg = 'therapies';
        elseif (str_contains($t, 'gift') || str_contains($tags, 'gift')) $seg = 'therapies';

        $slug = Str::slug($product->title ?: (string) $product->id);
        return url('/' . trim($seg, '/') . '/' . $product->id . '-' . $slug);
    }
}
