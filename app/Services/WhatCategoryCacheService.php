<?php

namespace App\Services;

use App\Models\OfferingV3;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WhatCategoryCacheService
{
    public function load(): array
    {
        $path = $this->catalogPath();

        if (File::exists($path)) {
            $decoded = json_decode((string) File::get($path), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $this->build();
    }

    public function export(?string $path = null): string
    {
        $path = $path ?: $this->catalogPath();
        $catalog = $this->build();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $path;
    }

    public function catalogPath(): string
    {
        return public_path('cache/what-categories.json');
    }

    public function build(): array
    {
        $productCounts = Product::query()
            ->whereHas('status', function ($status): void {
                $status->whereIn('status', ['live', 'approved']);
            })
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id')
            ->map(fn ($value): int => (int) $value)
            ->all();

        $offeringCounts = OfferingV3::query()
            ->whereIn('status', ['live', 'approved'])
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id')
            ->map(fn ($value): int => (int) $value)
            ->all();

        $categories = ProductCategory::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(function (ProductCategory $category) use ($productCounts, $offeringCounts): ?array {
                $name = trim((string) $category->name);
                if ($name === '') {
                    return null;
                }

                $products = (int) ($productCounts[$category->id] ?? 0);
                $offerings = (int) ($offeringCounts[$category->id] ?? 0);
                $total = $products + $offerings;

                if ($total <= 0) {
                    return null;
                }

                $slug = Str::slug($name);

                return [
                    'cat' => 'Modalities',
                    'title' => $name,
                    'label' => $name,
                    'value' => $name,
                    'type' => 'Modality',
                    'subtitle' => $total > 0 ? $total . ' offerings' : '',
                    'slug' => $slug,
                    'search' => implode(' ', array_filter([$name, $slug])),
                    'counts' => [
                        'products' => $products,
                        'offerings' => $offerings,
                        'total' => $total,
                    ],
                ];
            })
            ->filter()
            ->sort(function (array $left, array $right): int {
                $leftTotal = (int) data_get($left, 'counts.total', 0);
                $rightTotal = (int) data_get($right, 'counts.total', 0);

                if ($leftTotal !== $rightTotal) {
                    return $rightTotal <=> $leftTotal;
                }

                return strcasecmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
            })
            ->values()
            ->all();

        return [
            'generated_at' => now()->toIso8601String(),
            'count' => count($categories),
            'categories' => $categories,
        ];
    }
}
