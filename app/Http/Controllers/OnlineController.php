<?php

namespace App\Http\Controllers;

use App\Models\OfferingV3;
use App\Models\Product;
use App\Support\EventListing;
use App\Support\ProductRanking;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class OnlineController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'format'   => 'online',
            'sort'     => (string) $request->query('sort', ''),
            'page'     => max(1, (int) $request->query('page', 1)),
            'per_page' => min(48, max(8, (int) $request->query('per_page', 24))),
        ];

        $results = $this->fetchOfferings($filters);

        $hasFacets = (bool) (
            $filters['sort'] ||
            $request->has('page') ||
            $request->has('per_page')
        );

        return view('online.index', [
            'seo' => [
                'title' => 'Online Experiences | We Offer Wellness™',
                'description' => 'Join online wellness experiences from trusted practitioners — calming, convenient, and ready wherever you are.',
                'robots' => $hasFacets ? 'noindex,follow' : 'index,follow',
                'canonical' => url('/online'),
            ],
            'filters' => $filters,
            'results' => $results,
        ]);
    }

    private function fetchOfferings(array $query): array
    {
        $cacheKey = 'online:list:local:' . md5(json_encode($query));

        return $this->rememberSafely($cacheKey, now()->addMinutes(5), function () use ($query) {
            return $this->buildOfferingsPage($query);
        });
    }

    private function buildOfferingsPage(array $query): array
    {
        $items = $this->localOnlineItems();
        $sorted = ProductRanking::sortCollection($items, (string) ($query['sort'] ?? 'popular'))->values();

        $perPage = max(8, min((int) ($query['per_page'] ?? 24), 48));
        $currentPage = max(1, (int) ($query['page'] ?? 1));
        $total = $sorted->count();
        $lastPage = max(1, (int) ceil(max($total, 1) / $perPage));
        $currentPage = min($currentPage, $lastPage);

        $pageItems = $sorted
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        return [
            'items' => $pageItems->all(),
            'meta' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'total' => $total,
                'per_page' => $perPage,
                'total_pages' => $lastPage,
            ],
        ];
    }

    private function localOnlineItems(): Collection
    {
        $products = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->withMax('variants', 'price')
            ->with(['media', 'options.values', 'category', 'vendor.tiers', 'vendor.user.settings'])
            ->where(function ($q): void {
                $q->whereHas('status', function ($qs): void {
                    $qs->whereIn('status', ['live', 'approved']);
                })->orWhereNull('product_status_id');
            })
            ->whereHas('options', function ($oq): void {
                $oq->where('meta_name', 'locations')
                    ->whereHas('values', function ($vq): void {
                        $vq->whereRaw('LOWER(value) = ?', ['online']);
                    });
            })
            ->get()
            ->reject(fn (Product $product) => EventListing::isPast($product))
            ->filter(function (Product $product): bool {
                return method_exists($product, 'hasDisplayableImage')
                    ? $product->hasDisplayableImage()
                    : true;
            })
            ->map(function (Product $product): Product {
                $product->source_version = 'v1-v2';
                return $product;
            })
            ->values();

        $offerings = OfferingV3::query()
            ->with(['category', 'type', 'vendor.tiers', 'vendor.user.settings', 'media', 'coverMedia'])
            ->whereIn('status', ['live', 'approved'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get()
            ->reject(fn (OfferingV3 $offering) => EventListing::isPast($offering))
            ->filter(function (OfferingV3 $offering): bool {
                $locations = $offering->getLocations();
                return in_array('Online', $locations, true);
            })
            ->filter(function (OfferingV3 $offering): bool {
                return method_exists($offering, 'hasDisplayableImage')
                    ? $offering->hasDisplayableImage()
                    : true;
            })
            ->map(function (OfferingV3 $offering): OfferingV3 {
                $offering->source_version = 'v3';
                return $offering;
            })
            ->values();

        return $products->concat($offerings)->values();
    }

    private function rememberSafely(string $key, mixed $ttl, callable $callback): mixed
    {
        try {
            if (Cache::has($key)) {
                return Cache::get($key);
            }
        } catch (\Throwable $e) {
            // Cache backend unavailable or not writable. Fall back to live data.
        }

        $value = $callback();

        try {
            Cache::put($key, $value, $ttl);
        } catch (\Throwable $e) {
            // Ignore cache write failures.
        }

        return $value;
    }
}
