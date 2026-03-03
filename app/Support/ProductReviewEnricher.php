<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

class ProductReviewEnricher
{
    /**
     * Attach vendor-level review stats (avg, count) to products for display.
     * Sets attributes: reviews_avg_rating, reviews_count
     * Accepts a Collection, array, or Paginator of Product models.
     *
     * @param  mixed  $items
     * @return mixed  The same object with enriched items
     */
    public static function enrich($items)
    {
        if ($items instanceof LengthAwarePaginator || $items instanceof Paginator) {
            $items->setCollection(self::enrichCollection($items->getCollection()));
            return $items;
        }
        if ($items instanceof Collection) {
            return self::enrichCollection($items);
        }
        if (is_array($items)) {
            $coll = collect($items);
            $enriched = self::enrichCollection($coll);
            return $enriched->all();
        }
        return $items;
    }

    /**
     * @param  \Illuminate\Support\Collection  $collection
     * @return \Illuminate\Support\Collection
     */
    public static function enrichCollection(Collection $collection): Collection
    {
        return $collection->map(function ($item) {
            if ($item instanceof Product) {
                try {
                    $stats = $item->vendorReviewStats();
                    if (isset($stats['count'])) {
                        $item->setAttribute('reviews_count', (int) $stats['count']);
                    }
                    if (array_key_exists('avg', $stats)) {
                        $item->setAttribute('reviews_avg_rating', $stats['avg']);
                    }
                } catch (\Throwable $e) {
                    // Leave as-is on failure
                }
            }
            return $item;
        });
    }
}

