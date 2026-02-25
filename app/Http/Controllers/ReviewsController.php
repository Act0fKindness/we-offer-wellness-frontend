<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Inertia\Inertia;

class ReviewsController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['user:id,first_name,last_name,name,location', 'product:id,title,slug', 'vendor:id,vendor_name'])
            ->whereNotNull('review_text')
            ->orderByDesc('created_at')
            ->get()
            ->map(function(Review $review) {
                $user = $review->user;
                $customerName = $user ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : null;
                if (!$customerName) {
                    $customerName = $user?->name ?: 'Verified client';
                }
                return [
                    'id' => $review->id,
                    'quote' => trim($review->review_text),
                    'rating' => $review->rating ? (int) $review->rating : null,
                    'customer' => $customerName,
                    'location' => $user->location ?? null,
                    'product' => $review->product?->title,
                    'product_slug' => $review->product?->slug,
                    'vendor' => $review->vendor?->vendor_name,
                    'created_at' => optional($review->created_at)->toIso8601String(),
                ];
            });

        return Inertia::render('Reviews/Index', [
            'reviews' => $reviews,
            'meta' => [
                'title' => 'Client Reviews | We Offer Wellness',
                'description' => 'Read real stories from people who booked therapies, classes, workshops and retreats with We Offer Wellness.',
            ],
        ]);
    }
}

