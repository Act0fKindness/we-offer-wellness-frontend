<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Support\Str;

class OfferingsHubController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::query()
            ->withCount([
                'products as offerings_count' => function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereHas('status', function ($qs) {
                            $qs->whereIn('status', ['live', 'approved']);
                        })->orWhereNull('product_status_id');
                    });
                },
            ])
            ->orderByDesc('offerings_count')
            ->orderBy('name')
            ->take(8)
            ->get()
            ->map(function (ProductCategory $category) {
                $name = (string) ($category->name ?? '');
                return [
                    'title' => $name,
                    'slug' => Str::slug($name),
                    'count' => (int) ($category->offerings_count ?? 0),
                    'path' => url('/' . Str::slug($name)),
                    'tagline' => (string) ($category->tagline ?? ''),
                ];
            })
            ->filter(fn (array $category) => $category['title'] !== '')
            ->values()
            ->all();

        $types = [
            ['title' => 'Therapies', 'path' => '/therapies', 'copy' => '1-to-1 sessions and trusted support.'],
            ['title' => 'Classes', 'path' => '/classes', 'copy' => 'Repeatable practices and weekly rhythm.'],
            ['title' => 'Workshops', 'path' => '/workshops', 'copy' => 'Learn, try and go deeper together.'],
            ['title' => 'Events', 'path' => '/events', 'copy' => 'Live gatherings, pop-ups and special events.'],
            ['title' => 'Retreats', 'path' => '/retreats', 'copy' => 'Reset with day and weekend escapes.'],
            ['title' => 'Gifts', 'path' => '/giftcards', 'copy' => 'Digital gift cards and curated gifting.'],
        ];

        $locations = collect(app(LocationsController::class)->locationPages())
            ->filter(fn (array $location) => !empty($location['path']) && !empty($location['title']))
            ->take(8)
            ->map(function (array $location) {
                return [
                    'title' => (string) ($location['title'] ?? ''),
                    'path' => (string) ($location['path'] ?? ''),
                    'subtitle' => (string) ($location['county'] ?? $location['region'] ?? $location['country'] ?? ''),
                    'online' => (bool) ($location['online'] ?? false),
                ];
            })
            ->values()
            ->all();

        $painpoints = [
            ['title' => 'Stress & anxiety', 'slug' => 'stress-and-anxiety', 'copy' => 'Calm-first support when it all feels too much.'],
            ['title' => 'Sleep issues', 'slug' => 'sleep-issues', 'copy' => 'Restorative sessions for better rest and recovery.'],
            ['title' => 'Low mood & burnout', 'slug' => 'low-mood-burnout', 'copy' => 'Gentle care for depletion and emotional fatigue.'],
            ['title' => 'Overwhelm & frazzled feelings', 'slug' => 'overwhelm', 'copy' => 'For when your system needs more space and less noise.'],
            ['title' => 'Worry & racing thoughts', 'slug' => 'worry', 'copy' => 'Sessions that help slow spirals and restore focus.'],
            ['title' => 'Pain, tension & tightness', 'slug' => 'pain-management', 'copy' => 'Body-led support to ease tightness and discomfort.'],
        ];

        return view('offerings.index', [
            'categories' => $categories,
            'types' => $types,
            'locations' => $locations,
            'painpoints' => $painpoints,
        ]);
    }
}
