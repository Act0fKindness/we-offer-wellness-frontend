<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProvidersController extends Controller
{
    public function index()
    {
        return view('providers.index', [
            'seo' => [
                'title' => 'Practitioners | We Offer Wellness™',
                'description' => 'Discover trusted practitioners and facilitators across therapies and modalities.',
                'canonical' => url('/providers'),
            ],
        ]);
    }

    public function show(string $slug)
    {
        return view('providers.show', [
            'seo' => [
                'title' => 'Practitioner | We Offer Wellness™',
                'description' => 'Practitioner profile and offerings.',
                'canonical' => url('/provider/'.$slug),
                'robots' => 'noindex,follow',
            ],
            'slug' => $slug,
        ]);
    }
}

