<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
                'key' => 'sleep',
                'slug' => 'sleep',
                'title' => 'Sleep',
                'seo_title' => 'Sleep Support | We Offer Wellness™',
                'seo_description' => 'Wind down with therapies and experiences that support better sleep and rest.',
            ],
            [
                'key' => 'pain-management',
                'slug' => 'pain-management',
                'title' => 'Pain Management',
                'seo_title' => 'Pain Management | We Offer Wellness™',
                'seo_description' => 'Discover supportive therapies focused on easing discomfort and improving wellbeing.',
            ],
            [
                'key' => 'digestive-health',
                'slug' => 'digestive-health',
                'title' => 'Digestive Health',
                'seo_title' => 'Digestive Health | We Offer Wellness™',
                'seo_description' => 'Explore holistic approaches to digestion support, stress reduction and balance.',
            ],
            [
                'key' => 'energy-vitality',
                'slug' => 'energy-vitality',
                'title' => 'Energy & Vitality',
                'seo_title' => 'Energy & Vitality | We Offer Wellness™',
                'seo_description' => 'Boost your energy with uplifting experiences and whole-self wellbeing support.',
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

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($query) {
            $base = rtrim(config('services.wow.api_base', env('WOW_API_BASE', 'https://atease.weofferwellness.co.uk/api')), '/');

            $paths = ['/offerings', '/products', '/search'];

            foreach ($paths as $path) {
                $res = Http::acceptJson()
                    ->timeout(10)
                    ->get($base . $path, array_filter($query, fn ($v) => $v !== ''));

                if (!$res->ok()) {
                    continue;
                }

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
