<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;

class ReviewStatsController extends Controller
{
    public function index()
    {
        // Overall stats from product reviews
        $avg = (float) (ProductReview::query()->avg('rating') ?? 0);
        $avgRounded = $avg > 0 ? round($avg, 1) : null;
        $count = (int) (ProductReview::query()->count() ?? 0);

        // Verified count: ProductReview has no provider-added flag; treat all as verified
        $verifiedCount = $count;

        return response()->json([
            'avg_rating' => $avgRounded,
            'review_count' => $count,
            'verified_count' => $verifiedCount,
        ]);
    }
}
