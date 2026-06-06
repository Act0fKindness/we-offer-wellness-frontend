<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ReviewStatsController extends Controller
{
    public function index()
    {
        // Overall stats from canonical site reviews
        $query = Review::query()->whereRaw("TRIM(COALESCE(review_text, '')) <> ''");

        $avg = (float) ($query->avg('rating') ?? 0);
        $avgRounded = $avg > 0 ? round($avg, 1) : null;
        $count = (int) ($query->count() ?? 0);

        // Treat site reviews as verified feedback unless a future moderation flag changes that.
        $verifiedCount = $count;

        return response()->json([
            'avg_rating' => $avgRounded,
            'review_count' => $count,
            'verified_count' => $verifiedCount,
        ]);
    }
}
