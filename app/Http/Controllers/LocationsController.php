<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LocationsController extends Controller
{
    public function index(Request $request)
    {
        $locations = $this->locationsIndex();

        return view('locations.index', [
            'seo' => [
                'title' => 'Locations | We Offer Wellness™',
                'description' => 'Browse wellness experiences by location, including online options and locations near you.',
                'robots' => 'index,follow',
            ],
            'locations' => $locations,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $location = $this->findLocationBySlug($slug);
        abort_if($location === null, 404);

        $filters = [
            'location' => $location['key'],
            'format'   => (string) $request->query('format', ''),
            'sort'     => (string) $request->query('sort', ''),
            'page'     => max(1, (int) $request->query('page', 1)),
            'per_page' => min(48, max(8, (int) $request->query('per_page', 24))),
        ];

        $results = $this->fetchOfferings($filters);

        $hasFacets = (bool) (
            $filters['format'] ||
            $filters['sort'] ||
            $request->has('page') ||
            $request->has('per_page')
        );

        return view('locations.show', [
            'seo' => [
                'title' => $location['seo_title'] ?? ($location['title'] . ' | We Offer Wellness™'),
                'description' => $location['seo_description'] ?? ('Discover wellness experiences in ' . $location['title'] . '.'),
                'robots' => $hasFacets ? 'noindex,follow' : 'index,follow',
                'canonical' => url('/locations/' . $slug),
            ],
            'location' => $location,
            'filters' => $filters,
            'results' => $results,
        ]);
    }

    public function nearMe(Request $request)
    {
        // UX page (user-specific): enter postcode, then you can later resolve it to a nearest location.
        $postcode = trim((string) $request->query('postcode', ''));

        if ($postcode !== '') {
            $request->session()->put('near_me_postcode', $postcode);

            // Later: resolve postcode -> nearest /locations/{slug}
            return redirect()->route('locations.index', ['postcode' => $postcode]);
        }

        return view('near-me.index', [
            'seo' => [
                'title' => 'Near Me | We Offer Wellness™',
                'description' => 'Find wellness experiences near you. Enter your postcode to see what’s available locally.',
                'robots' => 'noindex,follow',
                'canonical' => url('/near-me'),
            ],
        ]);
    }

    private function locationsIndex(): array
    {
        return [
            [
                'key' => 'online',
                'slug' => 'online',
                'title' => 'Online',
                'seo_title' => 'Online Wellness Experiences | We Offer Wellness™',
                'seo_description' => 'Browse online experiences you can join from anywhere.',
            ],
            [
                'key' => 'london',
                'slug' => 'london',
                'title' => 'London',
                'seo_title' => 'Wellness in London | We Offer Wellness™',
                'seo_description' => 'Explore wellness experiences and therapies across London.',
            ],
            [
                'key' => 'kent',
                'slug' => 'kent',
                'title' => 'Kent',
                'seo_title' => 'Wellness in Kent | We Offer Wellness™',
                'seo_description' => 'Discover wellness experiences and therapies across Kent.',
            ],
            [
                'key' => 'manchester',
                'slug' => 'manchester',
                'title' => 'Manchester',
                'seo_title' => 'Wellness in Manchester | We Offer Wellness™',
                'seo_description' => 'Find wellness experiences and therapies across Manchester.',
            ],
        ];
    }

    private function findLocationBySlug(string $slug): ?array
    {
        foreach ($this->locationsIndex() as $location) {
            if (($location['slug'] ?? null) === $slug) {
                return $location;
            }
        }
        return null;
    }

    private function fetchOfferings(array $query): array
    {
        $cacheKey = 'locations:offerings:' . md5(json_encode($query));

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
