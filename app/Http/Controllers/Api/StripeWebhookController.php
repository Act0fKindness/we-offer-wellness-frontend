<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');
        $sig = $request->header('Stripe-Signature');
        $payload = $request->getContent();
        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Throwable $e) {
            Log::warning('stripe.webhook.invalid', ['message' => $e->getMessage()]);
            return response('invalid', 400);
        }

        $eventId = $event->id ?? null;
        if (!$eventId) return response('ok', 200);

        // Idempotency guard
        try {
            $dup = DB::table('stripe_webhook_events')->where('event_id', $eventId)->first();
            if ($dup) return response('ok', 200);
            DB::table('stripe_webhook_events')->insert([ 'event_id' => $eventId, 'type' => $event->type ?? null, 'created_at' => now(), 'updated_at' => now() ]);
        } catch (\Throwable $e) { Log::error('stripe.webhook.store_error', ['e'=>$e->getMessage()]); }

        $type = $event->type;
        try {
            switch ($type) {
                case 'checkout.session.completed':
                case 'checkout.session.async_payment_succeeded': {
                    $sess = $event->data->object;
                    $orderId = (int)($sess->metadata->order_id ?? 0);
                    if ($orderId) {
                        $order = Order::find($orderId);
                        if ($order) {
                            $order->status = 'paid';
                            $order->stripe_payment_intent_id = (string)($sess->payment_intent ?? '');
                            $order->save();
                        }
                    }
                    break;
                }
                case 'checkout.session.expired': {
                    $sess = $event->data->object; $orderId = (int)($sess->metadata->order_id ?? 0);
                    if ($orderId) { $o=Order::find($orderId); if ($o && $o->status==='pending') { $o->status='cancelled'; $o->save(); } }
                    break;
                }
                case 'checkout.session.async_payment_failed':
                case 'payment_intent.payment_failed': {
                    // Mark failed by client_reference if we can find session or by PI
                    $obj = $event->data->object; $pi = (string)($obj->id ?? $obj->payment_intent ?? '');
                    if ($pi) { $o = Order::where('stripe_payment_intent_id', $pi)->first(); if($o){ $o->status='failed'; $o->save(); } }
                    break;
                }
                case 'charge.refunded': {
                    $obj = $event->data->object; $pi = (string)($obj->payment_intent ?? '');
                    if ($pi) { $o = Order::where('stripe_payment_intent_id', $pi)->first(); if($o){ $o->status='refunded'; $o->save(); } }
                    break;
                }
                default:
                    Log::debug('stripe.webhook.unhandled', ['type' => $type]);
            }
        } catch (\Throwable $e) {
            Log::error('stripe.webhook.handle_error', ['type'=>$type, 'e'=>$e->getMessage()]);
        }

        return response('ok', 200);
    }
}

