<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ProductOrdering
{
    /**
     * Apply WOW's review-first ordering logic: reviewed listings first,
     * then by volume of reviews, vendor tenure, rating, and newest last.
     */
    public static function applyReviewPriority(EloquentBuilder|QueryBuilder $builder, array $options = []): void
    {
        $table = method_exists($builder, 'getModel')
            ? $builder->getModel()->getTable()
            : 'products';

        $tenureExpr = "COALESCE(TIMESTAMPDIFF(MONTH, (SELECT vd.created_at FROM vendor_details vd WHERE vd.id = {$table}.vendor_id LIMIT 1), NOW()), 0)";

        $builder->orderByRaw('CASE WHEN COALESCE(reviews_count, 0) > 0 THEN 1 ELSE 0 END DESC');
        $builder->orderByRaw('COALESCE(reviews_count, 0) DESC');
        $builder->orderByRaw($tenureExpr . ' DESC');
        $builder->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC');
        $builder->orderByDesc($table . '.id');
    }
}
