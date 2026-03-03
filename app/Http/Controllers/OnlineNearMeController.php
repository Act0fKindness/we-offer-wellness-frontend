<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnlineNearMeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('online-near-me.index', [
            'seo' => [
                'title' => 'Online & Near Me | We Offer Wellness™',
                'description' => 'Choose online experiences you can join anywhere, or find wellness experiences near you.',
                'robots' => 'index,follow',
                'canonical' => url('/online-near-me'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
