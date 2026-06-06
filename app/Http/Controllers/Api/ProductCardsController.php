<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfferingV3;
use App\Models\Product;
use App\Support\EventListing;
use App\Support\ProductRanking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCardsController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->integer('limit', 12);
        $limit = max(1, min($limit, 24));
        $pm = (float) $request->input('price_max', 50);
        $mode = strtolower((string) $request->input('mode', 'online'));

        $productQuery = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->with(['media','options.values','category', 'vendor.tiers', 'vendor.user.settings'])
            ->where(function ($w) {
                $w->whereHas('status', function ($qs) { $qs->whereIn('status', ['live','approved']); });
            });

        if ($mode === 'online') {
            $productQuery->whereHas('options', function ($oq) {
                $oq->where('meta_name', 'locations')
                   ->whereHas('values', function ($vq) { $vq->whereRaw("LOWER(value) = 'online'"); });
            });
        } elseif ($mode === 'in-person') {
            $productQuery->whereHas('options', function ($oq) {
                $oq->where('meta_name', 'locations')
                   ->whereHas('values', function ($vq) { $vq->whereRaw("LOWER(value) <> 'online'"); });
            });
        }

        $groupType = $request->has('group_type') ? $request->string('group_type')->toString() : null;
        if ($groupType) {
            \App\Support\ProductSearchFilters::applyWhoFilter($productQuery, null, $groupType);
        }

        $productQuery->where(function ($qq) use ($pm) {
            $qq->where(function ($qp) use ($pm) { $qp->where('price','<',1000)->where('price','<=',$pm); })
               ->orWhere(function ($qp) use ($pm) { $qp->where('price','>=',1000)->where('price','<=',$pm*100); })
               ->orWhereHas('variants', function ($qv) use ($pm) {
                   $qv->where(function ($qq2) use ($pm) { $qq2->where('price','<',1000)->where('price','<=',$pm); })
                      ->orWhere(function ($qq2) use ($pm) { $qq2->where('price','>=',1000)->where('price','<=',$pm*100); });
               });
        });

        $products = $productQuery->get()
            ->reject(fn (Product $product) => EventListing::isPast($product))
            ->filter(fn (Product $product) => method_exists($product, 'hasDisplayableImage') ? $product->hasDisplayableImage() : true)
            ->values();

        $offeringQuery = OfferingV3::query()
            ->with(['category', 'type', 'vendor.tiers', 'vendor.user.settings', 'media', 'coverMedia'])
            ->whereIn('status', ['live', 'approved']);

        $offerings = $offeringQuery->get()
            ->reject(fn ($offering) => EventListing::isPast($offering))
            ->filter(function ($offering) use ($pm, $mode, $groupType) {
            if (method_exists($offering, 'hasDisplayableImage') && ! $offering->hasDisplayableImage()) {
                return false;
            }

            $price = ProductRanking::priceValue($offering);
            if ($price === null || $price > $pm) {
                return false;
            }

            $locations = method_exists($offering, 'getLocations') ? $offering->getLocations() : [];
            $hasOnline = in_array('Online', $locations, true);
            $physical = array_values(array_filter($locations, fn ($location) => $location !== 'Online'));

            if (! $this->offeringMatchesGroupType($offering, $groupType)) {
                return false;
            }

            if ($mode === 'online') {
                return $hasOnline && count($physical) === 0;
            }

            if ($mode === 'in-person') {
                return count($physical) > 0;
            }

            return true;
        })->values();

        $products = ProductRanking::sortCollection($products->concat($offerings))->take($limit)->values();
        $html = '';
        foreach ($products as $p) {
            $html .= view('partials.product_card', ['product' => $p])->render();
        }
        return response($html)->header('Content-Type', 'text/html');
    }

    private function offeringMatchesGroupType(OfferingV3 $offering, ?string $groupType): bool
    {
        $groupType = strtolower(trim((string) $groupType));
        if (! in_array($groupType, ['solo', 'couple', 'group'], true)) {
            return true;
        }

        $rows = DB::table('offering_price_options')
            ->where('offering_id', $offering->id)
            ->select(['audience_type', 'pricing_type'])
            ->get();

        if ($rows->isNotEmpty()) {
            foreach ($rows as $row) {
                $audience = strtolower(trim((string) ($row->audience_type ?? '')));
                $pricing = strtolower(trim((string) ($row->pricing_type ?? '')));

                if ($groupType === 'solo' && ($audience === 'solo' || str_contains($pricing, 'solo'))) {
                    return true;
                }

                if ($groupType === 'couple' && ($audience === 'couple' || str_contains($pricing, 'couple'))) {
                    return true;
                }

                if ($groupType === 'group' && ($audience === 'group' || str_contains($pricing, 'group'))) {
                    return true;
                }
            }
        }

        $haystack = strtolower(trim(implode(' ', array_filter([
            (string) ($offering->title ?? ''),
            (string) ($offering->summary ?? ''),
            (string) ($offering->type?->name ?? ''),
            (string) ($offering->category?->name ?? ''),
        ]))));

        if ($groupType === 'solo') {
            return str_contains($haystack, 'solo') || str_contains($haystack, '1 person') || str_contains($haystack, '1-to-1') || str_contains($haystack, '1:1');
        }

        if ($groupType === 'couple') {
            return str_contains($haystack, 'couple') || str_contains($haystack, '2 person') || str_contains($haystack, 'pair') || str_contains($haystack, 'duo');
        }

        return str_contains($haystack, 'group') || str_contains($haystack, 'group session') || str_contains($haystack, 'workshop') || str_contains($haystack, 'class');
    }
}
