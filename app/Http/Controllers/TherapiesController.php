<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
                'key' => 'breathwork',
                'slug' => 'breathwork',
                'title' => 'Breathwork',
                'seo_title' => 'Breathwork | We Offer Wellness™',
                'seo_description' => 'Browse breathwork sessions designed to support calm, clarity and regulation.',
            ],
            [
                'key' => 'massage',
                'slug' => 'massage',
                'title' => 'Massage',
                'seo_title' => 'Massage | We Offer Wellness™',
                'seo_description' => 'Discover massage experiences for relaxation, recovery and whole-body care.',
            ],
            [
                'key' => 'meditation',
                'slug' => 'meditation',
                'title' => 'Meditation',
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

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($query) {
            $base = rtrim(config('services.wow.api_base', env('WOW_API_BASE', 'https://atease.weofferwellness.co.uk/api')), '/');

            $paths = ['/offerings', '/products', '/search'];

            foreach ($paths as $path) {
                $res = Http::acceptJson()
                    ->timeout(10)
                    ->get($base . $path, array_filter($query, fn ($v) => $v !== ''));

                if (!$res->ok()) continue;

                $json = $res->json();
                $items = $json['data'] ?? $json['items'] ?? (is_array($json) ? $json : []);
                $meta  = $json['meta'] ?? $json['pagination'] ?? [];

                if (is_array($items)) {
                    return ['items' => $items, 'meta' => $meta];
                }
            }

            return ['items' => [], 'meta' => []];
        });
    }
}
