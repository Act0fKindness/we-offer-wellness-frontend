<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductType;

class ProductTypeController extends Controller
{
    public function index()
    {
        // Prefer dedicated ProductType entries; fallback to distinct product_type from products
        $types = ProductType::query()->orderBy('name')->get(['id', 'name']);
        if ($types->isEmpty()) {
            $distinct = Product::query()
                ->whereNotNull('product_type')
                ->where('product_type', '!=', '')
                ->distinct()
                ->orderBy('product_type')
                ->pluck('product_type')
                ->map(fn($n) => ['id' => null, 'name' => $n]);
            return response()->json($distinct->values());
        }
        return response()->json($types->values());
    }
}

