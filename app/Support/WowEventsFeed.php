<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WowEventsFeed
{
    private const PATHS = ['/events', '/offerings', '/products', '/search'];

    /**
     * Fetch a list of events/workshops from the WOW API with lightweight caching.
     */
    public static function list(array $query = [], int $ttlMinutes = 3): array
    {
        $query = array_merge(['offering_kind' => 'event'], $query);
        $query = self::normaliseQuery($query);
        $cacheKey = 'wow:events:list:' . md5(json_encode($query));

        return Cache::remember($cacheKey, now()->addMinutes($ttlMinutes), function () use ($query) {
            $base = self::baseUrl();
            foreach (self::PATHS as $path) {
                $payload = self::attemptRequest($base . $path, $query);
                if ($payload !== null) {
                    return $payload + ['ok' => true];
                }
            }

            return ['items' => [], 'meta' => [], 'ok' => false];
        });
    }

    /**
     * Find a single event/workshop by slug.
     */
    public static function find(string $slug, int $ttlMinutes = 10): ?array
    {
        $cacheKey = 'wow:events:show:' . md5($slug);

        return Cache::remember($cacheKey, now()->addMinutes($ttlMinutes), function () use ($slug) {
            $base = self::baseUrl();
            $paths = [
                "/events/{$slug}",
                "/offerings/{$slug}",
                "/products/{$slug}",
                "/events/slug/{$slug}",
                "/offerings/slug/{$slug}",
                "/products/slug/{$slug}",
            ];

            foreach ($paths as $path) {
                $result = self::attemptRequest($base . $path, []);
                if ($result !== null && !empty($result['items'])) {
                    $first = $result['items'][0];
                    return is_array($first) ? $first : null;
                }
            }

            $search = self::list(['slug' => $slug, 'per_page' => 1]);
            $first = $search['items'][0] ?? null;

            return is_array($first) ? $first : null;
        });
    }

    /**
     * Determine whether a given filter set has any inventory. Returns null if the API was unreachable.
     */
    public static function hasInventory(array $query = [], int $ttlMinutes = 3): ?bool
    {
        $query = array_merge(['per_page' => max(1, (int)($query['per_page'] ?? 1))], $query);
        $results = self::list($query, $ttlMinutes);

        if (!($results['ok'] ?? false)) {
            return null;
        }

        $items = $results['items'] ?? [];
        if (is_array($items) && count($items)) {
            return true;
        }

        $meta = $results['meta'] ?? [];
        foreach (['total', 'total_count', 'total_items', 'count'] as $key) {
            if (isset($meta[$key]) && is_numeric($meta[$key])) {
                return ((int) $meta[$key]) > 0;
            }
        }

        return false;
    }

    private static function attemptRequest(string $url, array $query): ?array
    {
        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->get($url, $query);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$response->ok()) {
            return null;
        }

        $json = $response->json();
        if (!is_array($json)) {
            return ['items' => [], 'meta' => []];
        }

        $items = $json['data'] ?? $json['items'] ?? (array_is_list($json) ? $json : []);
        $meta = $json['meta'] ?? $json['pagination'] ?? [];

        if (!is_array($items)) {
            $items = [];
        } elseif (!array_is_list($items)) {
            $items = [$items];
        }
        if (!is_array($meta)) {
            $meta = [];
        }

        // Some detail endpoints return a single associative payload with attributes.
        if (!$items && isset($json['id'])) {
            $items = [$json];
        }

        return ['items' => array_values($items), 'meta' => $meta];
    }

    private static function normaliseQuery(array $query): array
    {
        $filtered = [];
        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
            }
            $filtered[$key] = $value;
        }
        ksort($filtered);

        return $filtered;
    }

    private static function baseUrl(): string
    {
        $base = config('services.wow.api_base');
        if (!$base) {
            $base = env('WOW_API_BASE', 'https://atease.weofferwellness.co.uk/api');
        }

        return rtrim((string) $base, '/');
    }
}
