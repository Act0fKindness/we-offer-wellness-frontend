<?php

namespace App\Http\Controllers;

use App\Models\OfferingV3;
use App\Models\Product;
use App\Models\ProductCategory;
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
            '/events',
            '/workshops',
            '/classes',
            '/retreats',
            '/gifts',
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
        ] as $p) {
            $urls[] = [ 'loc' => $base.$p, 'lastmod' => $now ];
        }

        try {
            foreach (app(LocationsController::class)->locationPages() as $location) {
                $path = (string) ($location['path'] ?? '');
                if ($path === '') {
                    continue;
                }

                $urls[] = [
                    'loc' => $base.$path,
                    'lastmod' => $now,
                ];
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
            '/gift-cards',
            '/corporate',
            '/corporate-wellness',
            '/search',
        ] as $p) {
            $urls[] = ['loc' => $base.$p, 'lastmod' => $now];
        }

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
}
