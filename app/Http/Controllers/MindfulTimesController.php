<?php

namespace App\Http\Controllers;

class MindfulTimesController extends Controller
{
    public function index()
    {
        return view('mindful-times.index', [
            'seo' => [
                'title' => 'Mindful Times | We Offer Wellness™',
                'description' => 'Guides, interviews and practical insights for everyday wellbeing.',
                'canonical' => url('/mindful-times'),
            ],
        ]);
    }
}

