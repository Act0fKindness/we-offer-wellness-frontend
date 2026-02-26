<?php

namespace App\Providers;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        View::composer('layouts.account', function ($view) {
            $reviews = Cache::remember('auth_review_snippets', now()->addMinutes(20), function () {
                return Review::with(['user:id,first_name,last_name,name,location', 'product:id,title'])
                    ->whereNotNull('review_text')
                    ->orderByDesc('created_at')
                    ->limit(12)
                    ->get()
                    ->map(function (Review $review) {
                        $user = $review->user;
                        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));
                        if (!$name) {
                            $name = $user?->name ?: 'Verified customer';
                        }
                        $location = $user?->location;
                        $rating = $review->rating ? (int) $review->rating : 5;
                        $rating = max(1, min(5, $rating));
                        $text = trim($review->review_text ?? '');
                        if ($text === '') {
                            return null;
                        }
                        $snippet = Str::limit($text, 240, '…');
                        $when = optional($review->created_at)->diffForHumans(null, false, false, 1) ?? 'Recently';

                        return [
                            'rating' => $rating,
                            'title' => $review->product?->title ?? 'Verified booking',
                            'text' => $snippet,
                            'name' => $name,
                            'location' => $location,
                            'when' => $when,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();
            });

            if (empty($reviews)) {
                $reviews = [[
                    'rating' => 5,
                    'title' => 'Genuinely effortless',
                    'text' => 'The filters actually helped. Booked an online session in under two minutes.',
                    'name' => 'Hannah',
                    'location' => 'Manchester',
                    'when' => 'Today',
                ]];
            }

            $view->with('authReviews', $reviews);
        });
    }
}
