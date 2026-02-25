<?php

namespace App\Http\Controllers;

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

        foreach (['/','/therapies','/events-workshops','/retreats','/gifts','/gift-cards','/corporate','/corporate-wellness','/search'] as $p) {
            $urls[] = [ 'loc' => $base.$p, 'lastmod' => $now ];
        }

        foreach (['london','manchester','birmingham','leeds','bristol','brighton','liverpool','glasgow','edinburgh','cardiff','kent'] as $city) {
            $urls[] = [ 'loc' => $base.'/'.rawurlencode($city), 'lastmod' => $now ];
        }

        try {
            $cats = ProductCategory::query()
                ->withCount('products')
                ->orderByDesc('products_count')->orderBy('name')
                ->limit(120)->get();
            foreach ($cats as $c) {
                $slug = Str::slug($c->name ?? '');
                if ($slug) $urls[] = [ 'loc' => $base.'/therapies/'.$slug, 'lastmod' => $now ];
            }
        } catch (\Throwable $e) {}

        try {
            $items = Product::query()->select(['id','title','product_type','tags_list','updated_at'])->latest('updated_at')->limit(1000)->get();
            foreach ($items as $p) {
                $t = strtolower((string) $p->product_type);
                $tags = strtolower((string) $p->tags_list);
                if (str_contains($t, 'workshop')) $seg = 'workshops';
                elseif (str_contains($t, 'event')) $seg = 'events';
                elseif (str_contains($t, 'class')) $seg = 'classes';
                elseif (str_contains($t, 'retreat')) $seg = 'retreats';
                elseif (str_contains($t, 'gift') || str_contains($tags, 'gift')) $seg = 'gifts';
                else $seg = 'therapies';
                $slug = Str::slug($p->title ?: (string)$p->id);
                $urls[] = [ 'loc' => $base.'/'.$seg.'/'.$p->id.'-'.$slug, 'lastmod' => optional($p->updated_at)->toAtomString() ?: $now ];
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
}

