<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CheckoutResultController extends Controller
{
    public function success(Request $request)
    {
        $orderId = (int) $request->query('order');
        $token = (string) $request->query('token', '');
        $order = Order::with('items')->find($orderId);
        if (!$order || !$token || !hash_equals($this->tokenForOrder($order), $token)) {
            abort(404);
        }

        session()->forget('cart.items');
        session()->forget('cart_promo_code');
        session()->forget('cart_gift_code');

        $response = response()->view('checkout.success', compact('order'));
        $response->withCookie(cookie('wow_cart', json_encode([]), 60*24*30));

        return $response;
    }

    public function cancel(Request $request)
    {
        $orderId = (int) $request->query('order');
        $order = $orderId ? Order::with('items')->find($orderId) : null;
        return view('checkout.cancel', compact('order'));
    }

    public static function tokenForOrder(Order $order): string
    {
        return hash_hmac('sha256', $order->id.'|'.$order->amount_total, config('app.key', 'secret'));
    }
}

class CheckoutResultController extends Controller
{
    //
}
