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
                $existingOrder = Order::with(['items', 'customerProfile'])->find($fresh->order_id);
                $this->queueOrderEmails($existingOrder, false);
                return $existingOrder;
            }

            $existing = $fresh->stripe_session_id
                ? Order::with(['items', 'customerProfile'])->where('stripe_session_id', $fresh->stripe_session_id)->first()
                : null;
            if ($existing) {
                $fresh->order_id = $existing->id;
                $fresh->status = 'completed';
                $fresh->save();
                $this->queueOrderEmails($existing, false);
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

            $this->queueOrderEmails($order, true);

            foreach ($fresh->items ?? [] as $id => $it) {
                $title = (string)($it['title'] ?? ('Item '.$id));
                $qty = max(1, (int)($it['qty'] ?? 1));
                $raw = (float)($it['price'] ?? 0);
                $unit = $raw >= 1000 ? (int)round($raw) : (int)round($raw * 100);
                $image = $it['image'] ?? $it['img'] ?? null;
                $productId = $it['product_id'] ?? $it['productId'] ?? null;
                $vendorId = $it['vendor_id'] ?? $it['vendorId'] ?? null;
                $variantLabel = $it['variant_label'] ?? null;
                $variantOptions = $it['options'] ?? [];
                if (!is_array($variantOptions)) {
                    $variantOptions = [];
                }
                $meta = array_filter([
                    'url' => $it['url'] ?? null,
                    'image' => $image,
                    'variant_label' => $variantLabel,
                    'variant_options' => $variantOptions,
                    'product_id' => $productId,
                    'vendor_id' => $vendorId,
                ], function ($value) {
                    return !is_null($value) && $value !== '' && $value !== [];
                });
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'vendor_id' => $vendorId,
                    'name' => $title,
                    'sku' => (string)$id,
                    'unit_amount' => $unit,
                    'quantity' => $qty,
                    'meta' => $meta,
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

    protected function queueOrderEmails(?Order $order, bool $sendReceipt): void
    {
        if (!$order) {
            return;
        }

        DB::afterCommit(function () use ($order, $sendReceipt) {
            $freshOrder = $order->fresh(['items', 'customerProfile']);
            if (!$freshOrder) {
                return;
            }

            if ($sendReceipt) {
                TransactionalMail::orderReceipt($freshOrder);
            }

            $this->sendVendorEmailsOnce($freshOrder);
        });
    }

    protected function sendVendorEmailsOnce(Order $order): void
    {
        if ($order->status !== 'paid') {
            return;
        }

        $updates = [];

        if (!$order->vendor_notified_at) {
            if (TransactionalMail::vendorOrderNotification($order)) {
                $updates['vendor_notified_at'] = now();
            }
        }

        if (!$order->vendor_introduction_sent_at) {
            if (TransactionalMail::vendorIntroduction($order)) {
                $updates['vendor_introduction_sent_at'] = now();
            }
        }

        if (!empty($updates)) {
            $order->forceFill($updates)->save();
        }
    }
}
