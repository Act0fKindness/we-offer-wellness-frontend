<?php

namespace App\Http\Controllers;

use App\Models\OfferingV3;
use App\Models\Product;
use App\Services\LocationCatalogService;
use App\Services\WhatCategoryCacheService;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function index()
    {
        $base = url('');
        $now = now()->toAtomString();
        $urls = [];

        foreach ([
            '/',
            '/therapies',
            '/holistic-therapies-uk',
            '/events',
            '/workshops',
            '/classes',
            '/retreats',
            '/gifts',
            '/giftcards',
            '/gift-cards',
            '/corporate',
            '/corporate-wellness',
            '/locations',
            '/online',
            '/online-near-me',
            '/near-me',
            '/needs',
            '/plan',
            '/search',
            '/reiki-near-me',
            '/sound-healing-near-me',
            '/holistic-therapy-near-me',
            '/wellness-classes-near-me',
        ] as $p) {
            $urls[] = [ 'loc' => $base.$p, 'lastmod' => $now ];
        }

        try {
            $catalog = app(LocationCatalogService::class)->load();
            foreach ((array) data_get($catalog, 'countries', []) as $country) {
                if ((int) data_get($country, 'counts.total', 0) > 0 && !empty($country['path'])) {
                    $urls[] = ['loc' => $base . (string) $country['path'], 'lastmod' => $now];
                }

                foreach ((array) data_get($country, 'counties', []) as $county) {
                    if ((int) data_get($county, 'counts.total', 0) > 0 && !empty($county['path'])) {
                        $urls[] = ['loc' => $base . (string) $county['path'], 'lastmod' => $now];
                    }

                    foreach ((array) data_get($county, 'towns', []) as $town) {
                        if ((int) data_get($town, 'counts.total', 0) > 0 && !empty($town['path'])) {
                            $urls[] = ['loc' => $base . (string) $town['path'], 'lastmod' => $now];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {}

        try {
            $nearMeCategories = app(WhatCategoryCacheService::class)->build();
            $catalog = app(LocationCatalogService::class)->load();
            $locationNodes = $this->nearMeLocationNodes($catalog);

            foreach ((array) data_get($nearMeCategories, 'categories', []) as $category) {
                $slug = Str::slug((string) ($category['slug'] ?? ''));
                $total = (int) data_get($category, 'counts.total', 0);

                if ($slug === '' || $total <= 0) {
                    continue;
                }

                $urls[] = [
                    'loc' => $base . '/' . $slug . '-near-me',
                    'lastmod' => $now,
                ];

                foreach ($locationNodes as $locationNode) {
                    $locationPath = (string) ($locationNode['path'] ?? '');
                    if ($locationPath === '') {
                        continue;
                    }

                    $urls[] = [
                        'loc' => $base . '/' . $slug . '-near-me' . Str::after($locationPath, '/locations'),
                        'lastmod' => $now,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        $urls = collect($urls)->unique('loc')->values()->all();

        try {
            $catalogProducts = Product::query()
                ->select(['id', 'title', 'product_type', 'tags_list', 'updated_at', 'category_id'])
                ->whereHas('status', function ($q) {
                    $q->whereIn('status', ['live', 'approved']);
                })
                ->with(['category:id,name'])
                ->get();

            $catalogProducts
                ->filter(fn (Product $product): bool => $product->category !== null)
                ->groupBy(fn (Product $product): string => Str::slug((string) ($product->category?->name ?? '')))
                ->each(function ($rows, string $categorySlug) use (&$urls, $base, $now) {
                    if ($categorySlug === '') {
                        return;
                    }

                    $categoryUpdated = optional($rows->max('updated_at'))->toAtomString() ?: $now;
                    $urls[] = [
                        'loc' => $base . '/' . $categorySlug . '/',
                        'lastmod' => $categoryUpdated,
                    ];

                    $typeSegments = $rows->map(function (Product $product): string {
                        return $this->typeSegmentFromRecord((string) ($product->product_type ?? ''), (string) ($product->tags_list ?? ''));
                    })->filter()->unique()->values();

                    foreach ($typeSegments as $typeSegment) {
                        $urls[] = [
                            'loc' => $base . '/' . $categorySlug . '/' . $typeSegment . '/',
                            'lastmod' => $categoryUpdated,
                        ];
                    }
                });
        } catch (\Throwable $e) {}

        try {
            $items = Product::query()->select(['id','title','product_type','tags_list','updated_at'])->latest('updated_at')->get();
            foreach ($items as $p) {
                $slug = Str::slug($p->title ?: (string)$p->id);
                $urls[] = [ 'loc' => $base.'/offerings/'.$p->id.'-'.$slug, 'lastmod' => optional($p->updated_at)->toAtomString() ?: $now ];
            }
        } catch (\Throwable $e) {}

        try {
            $offerings = OfferingV3::query()
                ->whereIn('status', ['live', 'approved'])
                ->get();
            foreach ($offerings as $offering) {
                $slug = Str::slug($offering->title ?: (string) $offering->id);
                $urls[] = [
                    'loc' => $base . '/offerings/' . $offering->id . '-' . $slug,
                    'lastmod' => optional($offering->updated_at)->toAtomString() ?: $now,
                ];
            }
        } catch (\Throwable $e) {}

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            $xml .= '<url>'
                . '<loc>'.htmlspecialchars($u['loc'], ENT_XML1).'</loc>'
                . (isset($u['lastmod']) ? '<lastmod>'.htmlspecialchars($u['lastmod'], ENT_XML1).'</lastmod>' : '')
                . '</url>';
        }
        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function indexFile()
    {
        $base = url('');
        $now = now()->toAtomString();
        $sitemaps = [
            $base . '/sitemap.xml',
            $base . '/sitemap-pages.xml',
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($sitemaps as $sitemap) {
            $xml .= '<sitemap>'
                . '<loc>' . htmlspecialchars($sitemap, ENT_XML1) . '</loc>'
                . '<lastmod>' . htmlspecialchars($now, ENT_XML1) . '</lastmod>'
                . '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function pages()
    {
        $base = url('');
        $now = now()->toAtomString();
        $urls = [];

        foreach ([
            '/',
            '/about',
            '/contact',
            '/help',
            '/privacy',
            '/terms',
            '/cookies',
            '/refunds-and-cancellations',
            '/safety-and-contraindications',
            '/giftcards',
            '/gift-cards',
            '/corporate',
            '/corporate-wellness',
            '/search',
            '/holistic-therapies-uk',
            '/reiki-near-me',
            '/sound-healing-near-me',
            '/holistic-therapy-near-me',
            '/wellness-classes-near-me',
        ] as $p) {
            $urls[] = ['loc' => $base.$p, 'lastmod' => $now];
        }

        try {
            $nearMeCategories = app(WhatCategoryCacheService::class)->build();

            foreach ((array) data_get($nearMeCategories, 'categories', []) as $category) {
                $slug = Str::slug((string) ($category['slug'] ?? ''));
                $total = (int) data_get($category, 'counts.total', 0);

                if ($slug === '' || $total <= 0) {
                    continue;
                }

                $urls[] = [
                    'loc' => $base . '/' . $slug . '-near-me',
                    'lastmod' => $now,
                ];
            }
        } catch (\Throwable $e) {}

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $u) {
            $xml .= '<url>'
                . '<loc>'.htmlspecialchars($u['loc'], ENT_XML1).'</loc>'
                . '<lastmod>'.htmlspecialchars($u['lastmod'], ENT_XML1).'</lastmod>'
                . '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    private function typeSegmentFromRecord(string $productType, string $tagsList = ''): string
    {
        $t = strtolower(trim($productType));
        $tags = strtolower(trim($tagsList));

        if (str_contains($t, 'workshop')) {
            return 'workshops';
        }
        if (str_contains($t, 'event')) {
            return 'events';
        }
        if (str_contains($t, 'class')) {
            return 'classes';
        }
        if (str_contains($t, 'retreat')) {
            return 'retreats';
        }
        if (str_contains($t, 'gift') || str_contains($tags, 'gift')) {
            return 'gifts';
        }

        return 'therapies';
    }

    /**
     * @param array<string, mixed> $catalog
     * @return array<int, array<string, mixed>>
     */
    private function nearMeLocationNodes(array $catalog): array
    {
        $nodes = [];

        foreach ((array) data_get($catalog, 'countries', []) as $country) {
            if (!empty($country['path']) && !(bool) data_get($country, 'online', false) && (int) data_get($country, 'counts.total', 0) > 0) {
                $nodes[] = $country;
            }

            foreach ((array) data_get($country, 'counties', []) as $county) {
                if (!empty($county['path']) && (int) data_get($county, 'counts.total', 0) > 0) {
                    $nodes[] = $county;
                }

                foreach ((array) data_get($county, 'towns', []) as $town) {
                    if (!empty($town['path']) && (int) data_get($town, 'counts.total', 0) > 0) {
                        $nodes[] = $town;
                    }
                }
            }
        }

        return $nodes;
    }
}
