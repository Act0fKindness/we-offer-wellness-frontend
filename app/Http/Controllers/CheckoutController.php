<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    public function create(Request $request)
    {
        $items = session('cart.items', []);
        if (empty($items)) {
            return response()->json(['error' => 'Cart is empty'], 422);
        }

        $currency = 'gbp';
        $order = new Order();
        $order->currency = $currency;
        $order->status = 'pending';
        $order->email = $request->user()->email ?? null;
        $order->save();

        $amountTotal = 0;
        $lineItems = [];
        foreach ($items as $id => $it) {
            $name = $it['title'] ?? 'Item';
            $qty = max(1, (int)($it['qty'] ?? 1));
            $unit = (float)($it['price'] ?? 0);
            if ($unit < 1000) { $unit = (int) round($unit * 100); } // pounds -> pence
            else { $unit = (int) $unit; }
            $amountTotal += $unit * $qty;
            OrderItem::create([
                'order_id' => $order->id,
                'name' => $name,
                'sku' => (string)$id,
                'unit_amount' => $unit,
                'quantity' => $qty,
                'meta' => ['product_id' => $id, 'url' => $it['url'] ?? null],
            ]);
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [ 'name' => $name ],
                    'unit_amount' => $unit,
                ],
                'quantity' => $qty,
            ];
        }
        $order->amount_total = $amountTotal;
        $order->save();

        $stripe = new StripeClient(config('services.stripe.secret'));
        $success = rtrim(config('app.url'), '/') . '/checkout/success?session_id={CHECKOUT_SESSION_ID}';
        $cancel = rtrim(config('app.url'), '/') . '/checkout/cancel?order_id=' . $order->id;

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $success,
            'cancel_url' => $cancel,
            'client_reference_id' => (string) $order->id,
            'metadata' => [ 'order_id' => (string)$order->id ],
        ]);

        $order->stripe_session_id = $session->id ?? null;
        $order->save();

        // Optionally clear server cart; keep client cart until webhook confirms
        session()->forget('cart.items');

        if ($request->expectsJson()) {
            return response()->json(['url' => $session->url]);
        }
        return redirect()->away($session->url);
    }

    public function success(Request $request)
    {
        return view('cart.success');
    }

    public function cancel(Request $request)
    {
        $orderId = (int) $request->query('order_id');
        if ($orderId) {
            $order = Order::find($orderId);
            if ($order && $order->status === 'pending') { $order->status = 'cancelled'; $order->save(); }
        }
        return redirect('/cart')->with('cart_message', 'Checkout cancelled.');
    }
}

