<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreAbandonedCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\StoreAbandonedCartService;

class StoreAbandonedCartController extends Controller
{
    public function track(Request $request, StoreAbandonedCartService $service): JsonResponse
    {
        $data = $request->validate([
            'visitor_key' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['array'],
            'cart_total' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = $request->user();
        $visitorKey = trim((string) ($data['visitor_key'] ?? '')) ?: (string) Str::uuid();
        $email = $user?->email;
        $name = $user?->name ?: trim(($user?->first_name ?? '').' '.($user?->last_name ?? ''));

        $service->record(array_values($data['items']), $user?->id, $email, $name, $visitorKey);

        return response()->json(['ok' => true, 'visitor_key' => $visitorKey]);
    }

    public function identify(Request $request): JsonResponse
    {
        $token = trim((string) $request->input('token', ''));
        [$encoded, $signature] = array_pad(explode('.', $token, 2), 2, '');
        $secret = trim((string) config('services.mail_relay.token'));
        if ($encoded === '' || $signature === '' || !hash_equals(hash_hmac('sha256', $encoded, $secret), $signature)) {
            return response()->json(['ok' => false], 422);
        }
        $raw = strtr($encoded, '-_', '+/');
        $raw .= str_repeat('=', (4 - strlen($raw) % 4) % 4);
        $payload = json_decode(base64_decode($raw), true);
        if (!is_array($payload) || (int) ($payload['expires'] ?? 0) < now()->timestamp || !filter_var($payload['email'] ?? null, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['ok' => false], 422);
        }
        $visitorKey = trim((string) $request->input('visitor_key', ''));
        if ($visitorKey === '') return response()->json(['ok' => false], 422);
        $cart = StoreAbandonedCart::query()->firstOrNew(['visitor_key' => $visitorKey]);
        $cart->email = strtolower((string) $payload['email']);
        $cart->user_id = $request->user()?->id;
        $cart->save();
        return response()->json(['ok' => true]);
    }
}
