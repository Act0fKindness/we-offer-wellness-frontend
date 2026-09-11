<?php

namespace App\Http\Controllers;

class ScheduleDiscoveryController extends Controller
{
    public function index()
    {
        return view('schedule-discovery.index', [
            'seo' => [
                'title' => 'Schedule Discovery | We Offer Wellness™',
                'description' => 'Schedule Discovery placeholder page.',
                'canonical' => url('/schedule-discovery'),
            ],
        ]);
    }
}
