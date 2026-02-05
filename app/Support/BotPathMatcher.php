<?php

namespace App\Support;

use Illuminate\Http\Request;

class BotPathMatcher
{
    /**
     * Determine if the incoming request should be blocked entirely.
     */
    public static function shouldBlock(Request $request): bool
    {
        return self::matchesPath($request->path())
            || self::matchesUrl($request->fullUrl());
    }

    /**
     * Does the string look like a blocked path?
     */
    public static function matchesPath(?string $path): bool
    {
        $normalized = self::normalizePath($path);
        if ($normalized === null) {
            return false;
        }

        $lower = strtolower($normalized);

        foreach (self::exactPaths() as $exact) {
            if ($lower === $exact) {
                return true;
            }
        }

        foreach (self::pathPrefixes() as $prefix) {
            if (str_starts_with($lower, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract the path component from a URL and test it.
     */
    public static function matchesUrl(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        try {
            $parts = parse_url($url);
        } catch (\Throwable $e) {
            return false;
        }

        $path = $parts['path'] ?? null;
        if ($path === null) {
            return false;
        }

        return self::matchesPath($path);
    }

    /**
     * Cache normalised exact paths from config.
     */
    private static function exactPaths(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $cache = [];
        foreach ((array) config('bot-blocker.exact_paths', []) as $path) {
            $normalized = self::normalizePath($path);
            if ($normalized !== null) {
                $cache[] = strtolower($normalized);
            }
        }

        return $cache;
    }

    /**
     * Cache normalised prefixes from config.
     */
    private static function pathPrefixes(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $cache = [];
        foreach ((array) config('bot-blocker.path_prefixes', []) as $prefix) {
            $normalized = self::normalizePath($prefix);
            if ($normalized !== null) {
                $cache[] = strtolower($normalized);
            }
        }

        return $cache;
    }

    /**
     * Consistently format any incoming path (adds a single leading slash).
     */
    private static function normalizePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $trimmed = trim($path);
        if ($trimmed === '') {
            return null;
        }

        $normal = '/' . ltrim($trimmed, '/');
        $normal = preg_replace('#/+#', '/', $normal);
        if ($normal === false || $normal === '') {
            return null;
        }

        return $normal;
    }
}
