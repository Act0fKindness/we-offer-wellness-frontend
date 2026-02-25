<?php

namespace App\Http\Controllers;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact.index', [
            'seo' => [
                'title' => 'Contact | We Offer Wellness™',
                'description' => 'Get in touch with the team for support, partnerships or media.',
                'canonical' => url('/contact'),
            ],
        ]);
    }
}

