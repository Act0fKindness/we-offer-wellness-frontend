<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CheckoutController extends Controller
{
    public function createSession(Request $request)
    {
        $items = session('cart.items', []);
        if (empty($items)) {
            $cookie = $request->cookie('wow_cart');
            if ($cookie) { $restored = json_decode($cookie, true) ?: []; if (is_array($restored)) { $items = $restored; session(['cart.items' => $items]); } }
        }
        if (empty($items)) {
            return response()->json(['ok'=>false,'error'=>'empty_cart'], 400);
        }

        // Build line items and compute totals
        $currency = 'gbp';
        $lineItems = [];
        $amountTotal = 0;
        foreach ($items as $id => $it) {
            $title = (string)($it['title'] ?? ('Item '.$id));
            $qty = max(1, (int)($it['qty'] ?? 1));
            $raw = (float)($it['price'] ?? 0);
            // Normalise to integer minor units (pence)
            $unit = $raw >= 1000 ? (int)round($raw) : (int)round($raw * 100);
            $amountTotal += ($unit * $qty);
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [ 'name' => $title ],
                    'unit_amount' => $unit,
                ],
                'quantity' => $qty,
            ];
        }

        // Create an Order record
        $order = null;
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => optional($request->user())->id,
                'email' => optional($request->user())->email,
                'currency' => strtoupper($currency),
                'amount_total' => $amountTotal,
                'status' => 'pending',
            ]);
            foreach ($items as $id => $it) {
                $title = (string)($it['title'] ?? ('Item '.$id));
                $qty = max(1, (int)($it['qty'] ?? 1));
                $raw = (float)($it['price'] ?? 0);
                $unit = $raw >= 1000 ? (int)round($raw) : (int)round($raw * 100);
                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => $title,
                    'sku' => (string)$id,
                    'unit_amount' => $unit,
                    'quantity' => $qty,
                    'meta' => [ 'url' => $it['url'] ?? null, 'image' => $it['image'] ?? null ],
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('checkout.create.order_failed', ['e' => $e->getMessage()]);
            return response()->json(['ok'=>false,'error'=>'order_failed'], 500);
        }

        // Create Stripe Checkout Session
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeSession::create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'metadata' => [ 'order_id' => (string)$order->id ],
                'client_reference_id' => (string)$order->id,
                'success_url' => url('/cart?paid=1'),
                'cancel_url' => url('/cart?cancel=1'),
            ]);

            $order->stripe_session_id = $session->id ?? null;
            $order->save();

            return response()->json(['ok'=>true,'url'=>$session->url]);
        } catch (\Throwable $e) {
            Log::error('checkout.create.stripe_failed', ['e' => $e->getMessage()]);
            return response()->json(['ok'=>false,'error'=>'stripe_failed'], 500);
        }
    }
}

