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
                'title' => 'Corporate Wellness 2026 | Join the Waiting List | We Offer Wellness®',
                'description' => 'Join the 2026 corporate wellness waiting list for workplace wellbeing days, employee rewards, team building and wellness workshops.',
                'keywords' => [
                    'corporate wellness',
                    'workplace wellbeing',
                    'employee rewards',
                    'team building activities',
                    'corporate gift vouchers',
                    'wellness workshops',
                    'wellbeing days',
                    'HR wellbeing',
                ],
                'canonical' => url('/corporate-wellness'),
                'robots' => 'noindex,follow',
            ],
        ]);
    }
}
