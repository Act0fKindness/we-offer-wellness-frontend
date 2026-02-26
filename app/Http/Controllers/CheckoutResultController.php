<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutResultController extends Controller
{
    public function success()
    {
        session()->forget('cart.items');
        session()->forget('cart_promo_code');
        session()->forget('cart_gift_code');

        $response = response()->view('checkout.success');
        $response->withCookie(cookie('wow_cart', json_encode([]), 60*24*30));

        return $response;
    }

    public function cancel()
    {
        $items = session('cart.items', []);
        if (empty($items)) {
            $cookie = request()->cookie('wow_cart');
            if ($cookie) {
                $restored = json_decode($cookie, true) ?: [];
                if (is_array($restored)) {
                    session(['cart.items' => $restored]);
                }
            }
        }
        return view('checkout.cancel');
    }
}

class CheckoutResultController extends Controller
{
    //
}
