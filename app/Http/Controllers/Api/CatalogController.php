<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfferingV3;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\VendorDetail;
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
                  ->with(['media', 'options.values', 'category', 'vendor.tiers'])
                  ->when(!$includeAll, fn($qq) => $qq->limit($productLimit));
            }])
            ->orderBy('name')
            ->get();

        $transformProduct = function (Product $p) {
            $locations = $p->getLocations();
            $isOnline = in_array('Online', $locations, true);
            $physicalLocations = array_values(array_filter($locations, fn($l) => $l !== 'Online'));
            $meta = $p->meta_json ?? [];
            $vendorPlan = $this->resolveVendorPlan($p->vendor);
            $t = strtolower((string) $p->product_type);
            $tags = strtolower((string) $p->tags_list);
            $slug = Str::slug($p->title ?: (string)$p->id);
            return [
                'id' => $p->id,
                'title' => $p->title,
                'type' => $p->product_type ?: 'experience',
                'category' => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
                'vendor_name' => $p->vendor?->vendor_name ?? null,
                'plan_key' => $vendorPlan['key'],
                'plan_label' => $vendorPlan['label'],
                'plan_priority' => $vendorPlan['priority'],
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
                'url' => url('/offerings/' . $p->id . '-' . $slug),
            ];
        };

        $transformOffering = function (OfferingV3 $offering) {
            $vendorPlan = $this->resolveVendorPlan($offering->vendor);
            $type = strtolower((string) ($offering->type?->name ?? $offering->category?->name ?? 'therapies'));
            $slug = Str::slug($offering->title ?: (string) $offering->id);

            return [
                'id' => $offering->id,
                'title' => $offering->title,
                'type' => $offering->type?->name ?? $offering->category?->name ?? 'experience',
                'category' => $offering->category ? ['id' => $offering->category->id, 'name' => $offering->category->name] : null,
                'vendor_name' => $offering->vendor?->vendor_name ?? null,
                'plan_key' => $vendorPlan['key'],
                'plan_label' => $vendorPlan['label'],
                'plan_priority' => $vendorPlan['priority'],
                'mode' => $this->offeringMode($offering),
                'location' => $offering->getLocations()[0] ?? null,
                'locations' => $offering->getLocations(),
                'price' => $offering->price ?? null,
                'price_min' => $offering->price ?? null,
                'price_max' => $offering->price ?? null,
                'compare_at_price' => null,
                'currency' => 'GBP',
                'rating' => null,
                'review_count' => 0,
                'image' => $offering->getFirstImageUrl(),
                'tags' => array_values(array_filter([
                    $offering->type?->name ?? null,
                    $offering->category?->name ?? null,
                ])),
                'url' => url('/offerings/' . $offering->id . '-' . $slug),
            ];
        };

        $data = $categories->map(function (ProductCategory $cat) use ($transformProduct) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'products' => $cat->products->map($transformProduct)->values(),
            ];
        })->values();

        $offerings = OfferingV3::query()
            ->with(['category', 'type', 'vendor.tiers', 'media', 'coverMedia'])
            ->whereIn('status', ['live', 'approved'])
            ->get()
            ->map($transformOffering)
            ->values();

        return response()->json([
            'categories' => $data,
            'offerings' => $offerings,
        ]);
    }

    private function resolveVendorPlan(?VendorDetail $vendor): array
    {
        $tierValue = null;

        try {
            $tier = null;
            if ($vendor) {
                if ($vendor->relationLoaded('tiers')) {
                    $tier = $vendor->tiers
                        ->sortByDesc(fn ($row) => $row->plan_started_at ?? $row->id ?? 0)
                        ->first();
                } else {
                    $tier = $vendor->tiers()->orderByDesc('plan_started_at')->orderByDesc('id')->first();
                }
            }
            $tierValue = (string) ($tier?->tier ?? '');
        } catch (\Throwable $e) {
            $tierValue = '';
        }

        $key = $this->canonicalPlanKey($tierValue);
        $isStarter = $key === 'starter' || $key === '';

        return [
            'key' => $key ?: 'starter',
            'label' => $this->planTitleForKey($key ?: 'starter'),
            'priority' => $isStarter ? 0 : 1,
        ];
    }

    private function canonicalPlanKey(?string $value): string
    {
        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace(['_', ' '], '-', $normalized);

        return match ($normalized) {
            'community', 'starter', 'standard', 'free-starter', 'starter-package' => 'starter',
            'core', 'business-accelerator', 'business-accelerator-package', 'businessaccelerator' => 'business-accelerator',
            'premium', 'premium-accelerator', 'premiumaccelerator' => 'premium-accelerator',
            'become-partner', 'partner' => 'become-partner',
            default => $normalized,
        };
    }

    private function planTitleForKey(?string $value): string
    {
        return match ($this->canonicalPlanKey($value)) {
            'starter' => 'Starter',
            'business-accelerator' => 'Business Accelerator',
            'premium-accelerator' => 'Premium Accelerator',
            'become-partner' => 'Become Partner',
            default => Str::headline(trim((string) $value)) ?: 'Plan',
        };
    }
}
