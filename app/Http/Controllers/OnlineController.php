<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OnlineController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'format'   => 'online',
            'sort'     => (string) $request->query('sort', ''),
            'page'     => max(1, (int) $request->query('page', 1)),
            'per_page' => min(48, max(8, (int) $request->query('per_page', 24))),
        ];

        $results = $this->fetchOfferings($filters);

        $hasFacets = (bool) (
            $filters['sort'] ||
            $request->has('page') ||
            $request->has('per_page')
        );

        return view('online.index', [
            'seo' => [
                'title' => 'Online Experiences | We Offer Wellness™',
                'description' => 'Join online wellness experiences from trusted practitioners — calming, convenient, and ready wherever you are.',
                'robots' => $hasFacets ? 'noindex,follow' : 'index,follow',
                'canonical' => url('/online'),
            ],
            'filters' => $filters,
            'results' => $results,
        ]);
    }

    private function fetchOfferings(array $query): array
    {
        $cacheKey = 'online:list:' . md5(json_encode($query));

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
