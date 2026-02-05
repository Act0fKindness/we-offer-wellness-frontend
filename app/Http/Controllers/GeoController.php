<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class GeoController extends Controller
{
    /**
     * Persist geolocation and mode preferences as cookies for a long period.
     */
    public function update(Request $request)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $city = (string) $request->input('city', '');
        $region = (string) $request->input('region', '');
        $country = (string) $request->input('country', '');
        $mode = (string) $request->input('mode', 'mixed'); // 'online' | 'mixed'

        $minutes = 60 * 24 * 365 * 5; // ~5 years

        $cookies = [
            Cookie::make('wow_lat', is_null($lat) ? '' : (string)$lat, $minutes, null, null, false, false, false, 'Lax'),
            Cookie::make('wow_lng', is_null($lng) ? '' : (string)$lng, $minutes, null, null, false, false, false, 'Lax'),
            Cookie::make('wow_city', $city, $minutes, null, null, false, false, false, 'Lax'),
            Cookie::make('wow_region', $region, $minutes, null, null, false, false, false, 'Lax'),
            Cookie::make('wow_country', $country, $minutes, null, null, false, false, false, 'Lax'),
            Cookie::make('wow_mode', in_array($mode, ['online','mixed']) ? $mode : 'mixed', $minutes, null, null, false, false, false, 'Lax'),
            Cookie::make('wow_geo_done', '1', $minutes, null, null, false, false, false, 'Lax'),
            Cookie::make('wow_geo_reask', '', -1), // clear re-ask flag
        ];

        return response()->json(['ok' => true])->withCookies($cookies);
    }
}

