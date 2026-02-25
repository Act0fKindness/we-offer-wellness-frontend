<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $format = strtolower((string) $request->query('format', ''));
        if ($format === 'online') {
            return redirect('/classes?mode=online&anytime=1', 302);
        }

        if ($request->has('category')) {
            $raw = trim((string) $request->query('category'));
            if ($raw !== '') {
                $slug = Str::slug($raw);
                $type = strtolower((string) $request->query('type', 'therapies'));
                $allowed = ['therapies','events','workshops','classes','retreats','gifts'];
                if (!in_array($type, $allowed, true)) { $type = 'therapies'; }
                return redirect('/'.$type.'/'.$slug, 302);
            }
        }

        if ($format && !$request->has('mode')) {
            $qs = $request->query();
            $qs['mode'] = $format;
            unset($qs['format']);
            $to = url('/search') . '?' . http_build_query($qs);
            return redirect($to, 302);
        }

        return view('search.index', [
            'mapsKey' => env('GOOGLE_MAPS_API_KEY'),
        ]);
    }
}

