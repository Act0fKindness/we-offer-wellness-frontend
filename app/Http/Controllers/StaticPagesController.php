<?php

namespace App\Http\Controllers;

class StaticPagesController extends Controller
{
    public function privacy() { return view('legal.privacy'); }
    public function terms() { return view('legal.terms'); }
    public function cookies() { return view('legal.cookies'); }
    public function refunds() { return view('legal.refunds-and-cancellations'); }
    public function partners() { return view('general.partners'); }
    public function giftCards() { return view('general.gift-cards'); }
}

