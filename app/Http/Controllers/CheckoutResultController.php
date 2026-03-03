<?php

namespace App\Http\Controllers;

use App\Models\CheckoutAttempt;
use App\Models\Order;
use App\Services\CheckoutOrderService;
use App\Services\TransactionalMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CheckoutResultController extends Controller
{
    public function __construct(private CheckoutOrderService $orderService)
    {
    }

    public function success(Request $request)
    {
        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId !== '') {
            return $this->successFromSession($sessionId);
        }

        return $this->legacySuccess($request);
    }

    protected function successFromSession(string $sessionId)
    {
        $order = Order::with('items')->where('stripe_session_id', $sessionId)->first();

        if (!$order) {
            $attempt = CheckoutAttempt::where('stripe_session_id', $sessionId)->first();
            if (!$attempt) {
                abort(404);
            }

            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = StripeSession::retrieve($sessionId);
            } catch (\Throwable $e) {
                Log::warning('checkout.success.session_fetch_failed', [
                    'session_id' => $sessionId,
                    'message' => $e->getMessage(),
                ]);
                abort(404);
            }

            if (($session->payment_status ?? null) !== 'paid') {
                abort(404);
            }

            $order = $this->orderService->finalizePaidAttempt($attempt, [
                'stripe_session_id' => $sessionId,
                'stripe_payment_intent_id' => (string)($session->payment_intent ?? ''),
            ]);
        }

        if (!$order) {
            abort(404);
        }

        session()->forget('cart.items');
        session()->forget('cart_promo_code');
        session()->forget('cart_gift_code');

        $response = response()->view('checkout.success', compact('order'));
        $response->withCookie(cookie('wow_cart', json_encode([]), 60*24*30));

        return $response;
    }

    protected function legacySuccess(Request $request)
    {
        $orderId = (int) $request->query('order');
        $token = (string) $request->query('token', '');
        $order = Order::with('items')->find($orderId);
        if (!$order || !$token || !hash_equals(self::tokenForOrder($order), $token)) {
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
        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId !== '') {
            $order = Order::with('items')->where('stripe_session_id', $sessionId)->first();
            $attempt = CheckoutAttempt::where('stripe_session_id', $sessionId)->first();
            if ($attempt && $attempt->status === 'pending') {
                $attempt->status = 'cancelled';
                $attempt->save();
                TransactionalMail::paymentCancelled($attempt, $order);
            } elseif ($order && $order->status === 'pending') {
                $order->status = 'cancelled';
                $order->save();
                TransactionalMail::paymentCancelled(null, $order);
            }
        } else {
            $orderId = (int) $request->query('order');
            $order = $orderId ? Order::with('items')->find($orderId) : null;
            if ($order && $order->status === 'pending') {
                $order->status = 'cancelled';
                $order->save();
                TransactionalMail::paymentCancelled(null, $order);
            }
        }

        return view('checkout.cancel', compact('order'));
    }

    public static function tokenForOrder(Order $order): string
    {
        return hash_hmac('sha256', $order->id.'|'.$order->amount_total, config('app.key', 'secret'));
    }
}
