<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ReviewStatsController extends Controller
{
    public function index()
    {
        // Overall stats
        $avg = (float) (Review::query()->avg('rating') ?? 0);
        $avgRounded = $avg > 0 ? round($avg, 1) : null;
        $count = (int) (Review::query()->count() ?? 0);

        // Verified = exclude provider-added if the flag exists
        $verifiedCount = (int) Review::query()
            ->where(function ($q) {
                $q->whereNull('is_provider_added')
                  ->orWhere('is_provider_added', false)
                  ->orWhere('is_provider_added', 0)
                  ->orWhere('is_provider_added', '0');
            })
            ->count();

        return response()->json([
            'avg_rating' => $avgRounded,
            'review_count' => $count,
            'verified_count' => $verifiedCount,
        ]);
    }
}

