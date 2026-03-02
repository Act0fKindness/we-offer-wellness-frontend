<?php

namespace App\Services;

use App\Models\CheckoutAttempt;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CheckoutOrderService
{
    public function finalizePaidAttempt(CheckoutAttempt $attempt, array $context = []): ?Order
    {
        return DB::transaction(function () use ($attempt, $context) {
            $fresh = CheckoutAttempt::whereKey($attempt->id)->lockForUpdate()->first();
            if (!$fresh) {
                return null;
            }

            if ($fresh->order_id) {
                return Order::with('items')->find($fresh->order_id);
            }

            $existing = $fresh->stripe_session_id
                ? Order::with('items')->where('stripe_session_id', $fresh->stripe_session_id)->first()
                : null;
            if ($existing) {
                $fresh->order_id = $existing->id;
                $fresh->status = 'completed';
                $fresh->save();
                return $existing;
            }

            $order = Order::create([
                'user_id' => $fresh->user_id,
                'email' => $fresh->email,
                'currency' => strtoupper($fresh->currency),
                'amount_total' => $fresh->amount_total,
                'status' => 'paid',
                'stripe_session_id' => $context['stripe_session_id'] ?? $fresh->stripe_session_id,
                'stripe_payment_intent_id' => $context['stripe_payment_intent_id'] ?? $fresh->stripe_payment_intent_id,
            ]);

            foreach ($fresh->items ?? [] as $id => $it) {
                $title = (string)($it['title'] ?? ('Item '.$id));
                $qty = max(1, (int)($it['qty'] ?? 1));
                $raw = (float)($it['price'] ?? 0);
                $unit = $raw >= 1000 ? (int)round($raw) : (int)round($raw * 100);
                $image = $it['image'] ?? $it['img'] ?? null;
                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => $title,
                    'sku' => (string)$id,
                    'unit_amount' => $unit,
                    'quantity' => $qty,
                    'meta' => [
                        'url' => $it['url'] ?? null,
                        'image' => $image,
                    ],
                ]);
            }

            $fresh->order_id = $order->id;
            $fresh->status = 'completed';
            $fresh->stripe_session_id = $order->stripe_session_id;
            $fresh->stripe_payment_intent_id = $order->stripe_payment_intent_id;
            $fresh->save();

            return $order->load('items');
        });
    }
}
