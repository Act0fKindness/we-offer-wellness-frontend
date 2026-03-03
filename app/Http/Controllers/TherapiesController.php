<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TherapiesController extends Controller
{
    public function index(Request $request)
    {
        $therapies = $this->therapiesIndex();

        return view('therapies.index', [
            'seo' => [
                'title' => 'Therapies | We Offer Wellness™',
                'description' => 'Explore holistic therapies and modalities, from sound healing and breathwork to massage and Reiki.',
                'robots' => 'index,follow',
            ],
            'therapies' => $therapies,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $therapy = $this->findTherapyBySlug($slug);
        abort_if($therapy === null, 404);

        $filters = [
            'therapy'  => $therapy['key'],
            'format'   => (string) $request->query('format', ''),
            'location' => (string) $request->query('location', ''),
            'sort'     => (string) $request->query('sort', ''),
            'page'     => max(1, (int) $request->query('page', 1)),
            'per_page' => min(48, max(8, (int) $request->query('per_page', 24))),
        ];

        $results = $this->fetchOfferings($filters);

        $hasFacets = (bool) (
            $filters['format'] ||
            $filters['location'] ||
            $filters['sort'] ||
            $request->has('page') ||
            $request->has('per_page')
        );

        return view('therapies.show', [
            'seo' => [
                'title' => $therapy['seo_title'] ?? ($therapy['title'] . ' | We Offer Wellness™'),
                'description' => $therapy['seo_description'] ?? ('Browse ' . $therapy['title'] . ' experiences and therapies.'),
                'robots' => $hasFacets ? 'noindex,follow' : 'index,follow',
                'canonical' => url('/therapies/' . $slug),
            ],
            'therapy' => $therapy,
            'filters' => $filters,
            'results' => $results,
        ]);
    }

    private function therapiesIndex(): array
    {
        return [
            [
                'key' => 'sound-healing',
                'slug' => 'sound-healing',
                'title' => 'Sound Healing',
                'seo_title' => 'Sound Healing | We Offer Wellness™',
                'seo_description' => 'Explore sound baths, gong sessions and vibrational therapy experiences.',
            ],
            [
                'key' => 'reiki',
                'slug' => 'reiki',
                'title' => 'Reiki',
                'seo_title' => 'Reiki | We Offer Wellness™',
                'seo_description' => 'Find Reiki sessions and energy-based experiences held by trusted practitioners.',
            ],
            [
                'key' => 'reflexology',
                'slug' => 'reflexology',
                'title' => 'Reflexology',
                'seo_title' => 'Reflexology | We Offer Wellness™',
                'seo_description' => 'Pressure-point bodywork and foot therapy to support circulation and relaxation.',
            ],
            [
                'key' => 'acupuncture',
                'slug' => 'acupuncture',
                'title' => 'Acupuncture',
                'seo_title' => 'Acupuncture | We Offer Wellness™',
                'seo_description' => 'Acupuncture and TCM-inspired sessions focusing on balance, repair and regulation.',
            ],
            [
                'key' => 'breathwork',
                'slug' => 'breathwork',
                'title' => 'Breathwork (1:1)',
                'seo_title' => 'Breathwork | We Offer Wellness™',
                'seo_description' => 'Browse breathwork sessions designed to support calm, clarity and regulation.',
            ],
            [
                'key' => 'massage',
                'slug' => 'massage',
                'title' => 'Massage Therapy',
                'seo_title' => 'Massage Therapy | We Offer Wellness™',
                'seo_description' => 'Discover massage experiences for relaxation, recovery and whole-body care.',
            ],
            [
                'key' => 'hypnotherapy',
                'slug' => 'hypnotherapy',
                'title' => 'Hypnotherapy',
                'seo_title' => 'Hypnotherapy | We Offer Wellness™',
                'seo_description' => 'Hypnotherapy sessions for habit change, calm and subconscious support.',
            ],
            [
                'key' => 'corporate-wellness',
                'slug' => 'corporate-wellness',
                'title' => 'Corporate Desk Reset',
                'seo_title' => 'Corporate Wellness | We Offer Wellness™',
                'seo_description' => 'On-site massage, breathwork and reset sessions curated for teams and events.',
            ],
            [
                'key' => 'somatic-experiencing',
                'slug' => 'somatic-experiencing',
                'title' => 'Somatic Experiencing',
                'seo_title' => 'Somatic Experiencing | We Offer Wellness™',
                'seo_description' => 'Body-first nervous system work to support release, grounding and resilience.',
            ],
            [
                'key' => 'meditation',
                'slug' => 'meditation',
                'title' => 'Meditation & Mindfulness',
                'seo_title' => 'Meditation | We Offer Wellness™',
                'seo_description' => 'Explore guided meditations and mindfulness experiences for everyday wellbeing.',
            ],
        ];
    }

    private function findTherapyBySlug(string $slug): ?array
    {
        foreach ($this->therapiesIndex() as $therapy) {
            if (($therapy['slug'] ?? null) === $slug) {
                return $therapy;
            }
        }
        return null;
    }

    private function fetchOfferings(array $query): array
    {
        $cacheKey = 'therapies:offerings:' . md5(json_encode($query));

        return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($query) {
            $therapyKey = $query['therapy'] ?? null;
            if (!$therapyKey) {
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
                ->where(function ($q) use ($therapyKey) {
                    $ids = $this->categoryIdsForTherapy($therapyKey);
                    if (!empty($ids)) {
                        $q->whereIn('category_id', $ids);
                    } else {
                        $slug = strtolower($therapyKey);
                        $q->whereRaw("LOWER(COALESCE(tags_list,'')) like ?", ['%'.$slug.'%'])
                          ->orWhereRaw("LOWER(COALESCE(meta_json->therapy_slug, '')) = ?", [$slug]);
                    }
                })
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
            $items = $builder->forPage($page, $perPage)->get();

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

    private function categoryIdsForTherapy(string $slug): array
    {
        $safe = strtolower($slug);
        $title = collect($this->therapiesIndex())
            ->firstWhere('slug', $slug)['title'] ?? $slug;

        return ProductCategory::query()
            ->where(function ($query) use ($safe, $title) {
                $query->whereRaw('LOWER(slug) like ?', [$safe.'%'])
                    ->orWhereRaw('LOWER(name) = ?', [strtolower($title)]);
            })
            ->pluck('id')
            ->all();
    }

}
