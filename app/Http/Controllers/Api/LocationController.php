<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorLocation;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $limit = max(1, min((int)$request->integer('limit', 12), 100));
        $search = trim((string)$request->query('search', $request->query('q', '')));

        $q = VendorLocation::query()
            ->selectRaw('city, county_region as region, country, COUNT(*) as c')
            ->whereNotNull('city')
            ->where('city', '!=', '');

        if ($search !== '') {
            $like = '%'.$search.'%';
            $q->where(function ($qb) use ($like) {
                $qb->where('city', 'like', $like)
                   ->orWhere('county_region', 'like', $like)
                   ->orWhere('country', 'like', $like);
            });
        }

        $rows = $q->groupBy('city', 'county_region', 'country')
            ->orderByDesc('c')
            ->limit($limit)
            ->get();

        $items = [];
        // Always include Online at the top
        $items[] = [
            'label' => 'Online',
            'city' => null,
            'region' => null,
            'country' => null,
            'value' => 'Online',
            'icon' => 'wifi',
        ];

        foreach ($rows as $r) {
            $city = (string) ($r->city ?? '');
            $region = (string) ($r->region ?? '');
            $country = (string) ($r->country ?? '');
            $parts = array_filter([$city, $region ?: null, $country ?: null]);
            $label = $city;
            $subtitle = $country ?: $region;
            $items[] = [
                'label' => $label,
                'subtitle' => $subtitle,
                'city' => $city,
                'region' => $region,
                'country' => $country,
                'value' => $city,
                'icon' => 'geo',
            ];
        }

        // Fallback presets if DB is empty (or thin envs)
        if (count($items) <= 1) { // only Online present
            $presets = [
                ['label' => 'London', 'subtitle' => 'United Kingdom', 'value' => 'London', 'icon' => 'geo'],
                ['label' => 'Manchester', 'subtitle' => 'United Kingdom', 'value' => 'Manchester', 'icon' => 'geo'],
                ['label' => 'Brighton & Hove', 'subtitle' => 'United Kingdom', 'value' => 'Brighton & Hove', 'icon' => 'geo'],
                ['label' => 'Kent', 'subtitle' => 'United Kingdom', 'value' => 'Kent', 'icon' => 'geo'],
            ];
            foreach ($presets as $p) { $items[] = $p; }
        }

        return response()->json(['status' => 'success', 'items' => $items]);
    }
}
