<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EventsController extends Controller
{
    public function index(Request $request)
    {
        $type = strtolower((string) $request->query('type', ''));
        $type = in_array($type, ['event', 'workshop'], true) ? $type : '';

        $filters = [
            'type'     => $type,
            'format'   => (string) $request->query('format', ''),
            'location' => (string) $request->query('location', ''),
            'date'     => (string) $request->query('date', ''),
            'sort'     => (string) $request->query('sort', ''),
            'page'     => max(1, (int) $request->query('page', 1)),
            'per_page' => min(48, max(8, (int) $request->query('per_page', 24))),
        ];

        $results = $this->fetchEvents($filters);

        $hasFacets = (bool) (
            $filters['type'] ||
            $filters['format'] ||
            $filters['location'] ||
            $filters['date'] ||
            $filters['sort'] ||
            $request->has('page') ||
            $request->has('per_page')
        );

        return view('events.index', [
            'seo' => [
                'title' => 'Events & Workshops | We Offer Wellness™',
                'description' => 'Discover upcoming wellness events and workshops, online and near you.',
                'robots' => $hasFacets ? 'noindex,follow' : 'index,follow',
                'canonical' => url('/events'),
            ],
            'filters' => $filters,
            'results' => $results,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $event = $this->fetchEventBySlug($slug);
        abort_if($event === null, 404);

        $title = (string) ($event['seo_title'] ?? $event['title'] ?? 'Event');

        return view('events.show', [
            'seo' => [
                'title' => $title . ' | We Offer Wellness™',
                'description' => (string) ($event['seo_description'] ?? $event['summary'] ?? 'Explore this event on We Offer Wellness™.'),
                'robots' => 'index,follow',
                'canonical' => url('/events/' . $slug),
            ],
            'event' => $event,
        ]);
    }

    private function fetchEvents(array $query): array
    {
        $cacheKey = 'events:list:' . md5(json_encode($query));

        return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($query) {
            $base = rtrim(config('services.wow.api_base', env('WOW_API_BASE', 'https://atease.weofferwellness.co.uk/api')), '/');

            // Try likely endpoints
            $paths = ['/events', '/offerings', '/products', '/search'];

            // Nudge generic endpoints to behave like events (if your API supports it)
            $query = array_merge(['offering_kind' => 'event'], $query);

            foreach ($paths as $path) {
                $res = Http::acceptJson()
                    ->timeout(10)
                    ->get($base . $path, array_filter($query, fn ($v) => $v !== ''));

                if (!$res->ok()) continue;

                $json  = $res->json();
                $items = $json['data'] ?? $json['items'] ?? (is_array($json) ? $json : []);
                $meta  = $json['meta'] ?? $json['pagination'] ?? [];

                if (is_array($items)) {
                    return ['items' => $items, 'meta' => $meta];
                }
            }

            return ['items' => [], 'meta' => []];
        });
    }

    private function fetchEventBySlug(string $slug): ?array
    {
        $cacheKey = 'events:show:' . $slug;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($slug) {
            $base = rtrim(config('services.wow.api_base', env('WOW_API_BASE', 'https://atease.weofferwellness.co.uk/api')), '/');

            $paths = [
                "/events/{$slug}",
                "/offerings/{$slug}",
                "/products/{$slug}",
                "/events/slug/{$slug}",
                "/offerings/slug/{$slug}",
                "/products/slug/{$slug}",
            ];

            foreach ($paths as $path) {
                $res = Http::acceptJson()->timeout(10)->get($base . $path);
                if (!$res->ok()) continue;

                $json = $res->json();
                if (is_array($json) && !empty($json)) {
                    return $json['data'] ?? $json;
                }
            }

            // Search fallback
            $search = $this->fetchEvents(['slug' => $slug, 'per_page' => 1, 'page' => 1]);
            $first = $search['items'][0] ?? null;

            return is_array($first) ? $first : null;
        });
    }
}
