<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SafetyContraindicationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        return view('legal.safety-and-contraindications', [
            'title' => 'Safety & Contraindications',
            'metaDescription' => 'Important safety information and contraindications for therapies and wellness experiences.',
            'canonical' => url('/safety-and-contraindications'),
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
