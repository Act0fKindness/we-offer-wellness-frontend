<?php

namespace App\Http\Controllers;

use App\Models\CheckoutAttempt;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CheckoutController extends Controller
{
    public function createSession(Request $request)
    {
        $items = [];

        $payload = $request->input('items');
        if (is_array($payload) && !empty($payload)) {
            $normalized = [];
            $isList = array_is_list($payload);
            foreach ($payload as $key => $entry) {
                if (!is_array($entry)) continue;
                $id = $entry['id'] ?? ($isList ? null : $key);
                if (!$id) continue;
                $normalized[(string)$id] = [
                    'id' => $id,
                    'product_id' => $entry['product_id'] ?? $entry['productId'] ?? null,
                    'vendor_id' => $entry['vendor_id'] ?? $entry['vendorId'] ?? null,
                    'variant_id' => $entry['variant_id'] ?? $entry['variantId'] ?? null,
                    'variant_label' => $entry['variant_label'] ?? $entry['options_label'] ?? null,
                    'title' => (string)($entry['title'] ?? ('Item '.$id)),
                    'price' => (float)($entry['price'] ?? $entry['unit'] ?? 0),
                    'qty' => max(1, (int)($entry['qty'] ?? $entry['quantity'] ?? 1)),
                    'image' => $entry['image'] ?? $entry['img'] ?? null,
                    'url' => $entry['url'] ?? '#',
                ];
            }
            if (!empty($normalized)) {
                $items = $normalized;
                session(['cart.items' => $items]);
            }
        }

        if (empty($items)) {
            $items = session('cart.items', []);
        }
        if (empty($items)) {
            $cookieRaw = $request->cookie('wow_cart');
            if ($cookieRaw) {
                $restored = json_decode($cookieRaw, true) ?: [];
                if (is_array($restored) && !empty($restored)) {
                    $items = $restored;
                    session(['cart.items' => $items]);
                } else {
                    Log::warning('checkout.cookie.decode_failed', [
                        'len' => strlen($cookieRaw),
                        'raw_sample' => substr($cookieRaw, 0, 120),
                        'error' => json_last_error_msg(),
                    ]);
                }
            }
        }
        if (empty($items)) {
            Log::warning('checkout.empty_cart', [
                'session_has' => session()->has('cart.items'),
                'cookie_present' => (bool) $request->cookie('wow_cart'),
            ]);
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
            $image = $it['image'] ?? $it['img'] ?? null;
            if ($image && !str_starts_with($image, 'http')) {
                $image = url($image);
            }
            $productData = [ 'name' => $title ];
            if ($image) {
                $productData['images'] = [$image];
            }
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => $productData,
                    'unit_amount' => $unit,
                ],
                'quantity' => $qty,
            ];
        }

        // Prepare checkout attempt (used to create the order only after payment succeeds)
        $attempt = null;
        $order = null;
        $guestEmail = trim((string)$request->input('email', ''));
        if ($guestEmail !== '' && !filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['ok'=>false,'error'=>'invalid_email'], 422);
        }
        $resolvedEmail = optional($request->user())->email ?: $guestEmail;
        if (!$resolvedEmail) {
            return response()->json(['ok'=>false,'error'=>'email_required'], 422);
        }
        if ($this->hasCheckoutAttemptsTable()) {
            try {
                $attempt = CheckoutAttempt::create([
                    'user_id' => optional($request->user())->id,
                    'email' => $resolvedEmail,
                    'currency' => strtoupper($currency),
                    'amount_total' => $amountTotal,
                    'items' => $items,
                    'status' => 'pending',
                    'meta' => [
                        'ip' => $request->ip(),
                        'user_agent' => substr((string)$request->userAgent(), 0, 255),
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::error('checkout.create.attempt_failed', ['e' => $e->getMessage()]);
                return response()->json(['ok'=>false,'error'=>'order_failed'], 500);
            }
        } else {
            try {
                $order = $this->createPendingOrderFromItems($request, $items, $currency, $amountTotal, $resolvedEmail);
            } catch (\Throwable $e) {
                Log::error('checkout.create.order_fallback_failed', ['e' => $e->getMessage()]);
                return response()->json(['ok'=>false,'error'=>'order_failed'], 500);
            }
        }

        // Create Stripe Checkout Session
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $metadata = [];
            if ($attempt) {
                $metadata['attempt_id'] = (string)$attempt->id;
            }
            if ($order) {
                $metadata['order_id'] = (string)$order->id;
            }

            $session = StripeSession::create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'metadata' => $metadata,
                'client_reference_id' => (string)($attempt?->id ?? $order?->id ?? ''),
                'customer_email' => $resolvedEmail,
                'success_url' => route('checkout.success', [], true).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.cancel', [], true).'?session_id={CHECKOUT_SESSION_ID}',
            ]);

            if ($attempt) {
                $attempt->stripe_session_id = $session->id ?? null;
                $attempt->save();
            }
            if ($order) {
                $order->stripe_session_id = $session->id ?? null;
                $order->save();
            }

            return response()->json(['ok'=>true,'url'=>$session->url]);
        } catch (\Throwable $e) {
            Log::error('checkout.create.stripe_failed', ['e' => $e->getMessage()]);
            if ($attempt) {
                $attempt->status = 'failed';
                $attempt->save();
            }
            if ($order && $order->status === 'pending') {
                $order->status = 'failed';
                $order->save();
            }
            return response()->json(['ok'=>false,'error'=>'stripe_failed'], 500);
        }
    }

    protected function hasCheckoutAttemptsTable(): bool
    {
        static $hasTable = null;
        if ($hasTable !== null) {
            return $hasTable;
        }

        try {
            $hasTable = Schema::hasTable((new CheckoutAttempt())->getTable());
        } catch (\Throwable $e) {
            Log::warning('checkout.create.attempt_table_check_failed', ['e' => $e->getMessage()]);
            $hasTable = false;
        }

        return $hasTable;
    }

    protected function createPendingOrderFromItems(
        Request $request,
        array $items,
        string $currency,
        int $amountTotal,
        string $resolvedEmail
    ): Order {
        return DB::transaction(function () use ($request, $items, $currency, $amountTotal, $resolvedEmail) {
            $orderPayload = [
                'user_id' => optional($request->user())->id,
                'email' => $resolvedEmail,
                'currency' => strtoupper($currency),
                'amount_total' => $amountTotal,
                'status' => 'pending',
            ];
            try {
                if (Schema::hasColumn('orders', 'total_price')) {
                    $orderPayload['total_price'] = round($amountTotal / 100, 2);
                }
            } catch (\Throwable $e) {
                Log::warning('checkout.create.orders_schema_check_failed', ['e' => $e->getMessage()]);
            }
            $order = Order::create($orderPayload);

            $hasProductId = false;
            $hasVendorId = false;
            $hasPrice = false;
            try {
                $hasProductId = Schema::hasColumn('order_items', 'product_id');
                $hasVendorId = Schema::hasColumn('order_items', 'vendor_id');
                $hasPrice = Schema::hasColumn('order_items', 'price');
            } catch (\Throwable $e) {
                Log::warning('checkout.create.order_items_schema_check_failed', ['e' => $e->getMessage()]);
            }

            foreach ($items as $id => $it) {
                $title = (string)($it['title'] ?? ('Item '.$id));
                $qty = max(1, (int)($it['qty'] ?? 1));
                $raw = (float)($it['price'] ?? 0);
                $unit = $raw >= 1000 ? (int)round($raw) : (int)round($raw * 100);
                $linePrice = round($unit / 100, 2);
                $image = $it['image'] ?? $it['img'] ?? null;
                $productId = $it['product_id'] ?? $it['productId'] ?? null;
                if (!$productId && isset($it['id'])) {
                    $itemId = (string)$it['id'];
                    if (is_numeric($itemId)) {
                        $productId = (int)$itemId;
                    } elseif (str_starts_with($itemId, 'p:') && is_numeric(substr($itemId, 2))) {
                        $productId = (int)substr($itemId, 2);
                    }
                }
                if (!$productId && is_string((string)$id) && str_starts_with((string)$id, 'p:') && is_numeric(substr((string)$id, 2))) {
                    $productId = (int)substr((string)$id, 2);
                }
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

                $payload = [
                    'order_id' => $order->id,
                    'name' => $title,
                    'sku' => (string)$id,
                    'unit_amount' => $unit,
                    'quantity' => $qty,
                    'meta' => $meta,
                ];
                if ($hasProductId) {
                    // Legacy schemas require a non-null product_id.
                    $payload['product_id'] = $productId ?: 0;
                }
                if ($hasVendorId) {
                    $payload['vendor_id'] = $vendorId;
                }
                if ($hasPrice) {
                    $payload['price'] = $linePrice;
                }

                OrderItem::create($payload);
            }

            return $order;
        });
    }
}
