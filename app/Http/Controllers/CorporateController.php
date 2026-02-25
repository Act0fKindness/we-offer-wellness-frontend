<?php

namespace App\Http\Controllers;

class CorporateController extends Controller
{
    public function hub()
    {
        return view('corporate.index', [
            'seo' => [
                'title' => 'Corporate Wellness | We Offer Wellness™',
                'description' => 'Workplace wellbeing programmes: workshops, meditation, breathwork and more.',
                'canonical' => url('/corporate'),
            ],
        ]);
    }

    public function comingSoon()
    {
        return view('corporate.coming-soon', [
            'seo' => [
                'title' => 'Corporate Wellness — Coming Soon | We Offer Wellness™',
                'description' => 'Corporate wellness services launching soon. Register interest.',
                'canonical' => url('/corporate-wellness'),
                'robots' => 'noindex,follow',
            ],
        ]);
    }
}

