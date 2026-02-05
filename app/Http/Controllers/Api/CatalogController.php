<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $productLimit = $request->integer('product_limit', 12);
        $includeAll = strtolower((string)$request->query('all', 'false')) === 'true';

        $categories = ProductCategory::query()
            ->with(['products' => function ($q) use ($productLimit, $includeAll) {
                $q->withCount('reviews')
                  ->withAvg('reviews', 'rating')
                  ->withMin('variants','price')
                  ->withMax('variants','price')
                  ->with(['media', 'options.values', 'category'])
                  ->when(!$includeAll, fn($qq) => $qq->limit($productLimit));
            }])
            ->orderBy('name')
            ->get();

        $transformProduct = function (Product $p) {
            $locations = $p->getLocations();
            $isOnline = in_array('Online', $locations, true);
            $physicalLocations = array_values(array_filter($locations, fn($l) => $l !== 'Online'));
            $meta = $p->meta_json ?? [];
            $t = strtolower((string) $p->product_type);
            $tags = strtolower((string) $p->tags_list);
            $typeSeg = 'therapies';
            if (str_contains($t, 'workshop')) $typeSeg = 'workshops';
            elseif (str_contains($t, 'event')) $typeSeg = 'events';
            elseif (str_contains($t, 'class')) $typeSeg = 'classes';
            elseif (str_contains($t, 'retreat')) $typeSeg = 'retreats';
            elseif (str_contains($t, 'gift') || str_contains($tags, 'gift')) $typeSeg = 'gifts';
            $slug = Str::slug($p->title ?: (string)$p->id);
            return [
                'id' => $p->id,
                'title' => $p->title,
                'type' => $p->product_type ?: 'experience',
                'category' => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
                'mode' => $isOnline && count($physicalLocations) === 0 ? 'Online' : (count($physicalLocations) ? 'In-person' : null),
                'location' => $physicalLocations[0] ?? ($isOnline ? 'Online' : null),
                'locations' => $locations,
                'price' => $p->price ?? null,
                'price_min' => $p->variants_min_price ?? ($p->price ?? null),
                'price_max' => $p->variants_max_price ?? ($p->price ?? null),
                'compare_at_price' => $meta['compare_at_price'] ?? null,
                'currency' => $meta['currency'] ?? 'GBP',
                'rating' => round((float)($p->reviews_avg_rating ?? 0), 1) ?: null,
                'review_count' => (int)($p->reviews_count ?? 0),
                'image' => $p->getFirstImageUrl(),
                'tags' => $p->tags_list ? array_map('trim', explode(',', $p->tags_list)) : [],
                'url' => url('/'.$typeSeg.'/' . $p->id . '-' . $slug),
            ];
        };

        $data = $categories->map(function (ProductCategory $cat) use ($transformProduct) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'products' => $cat->products->map($transformProduct)->values(),
            ];
        })->values();

        return response()->json($data);
    }
}
