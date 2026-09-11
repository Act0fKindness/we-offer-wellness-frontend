<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StoreAbandonedCart;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StoreAbandonedCartService
{
    public function record(array $items, ?int $userId = null, ?string $email = null, ?string $name = null, ?string $visitorKey = null): StoreAbandonedCart
    {
        $visitorKey = trim((string) $visitorKey) ?: (string) Str::uuid();
        $cart = StoreAbandonedCart::query()->firstOrNew(['visitor_key' => $visitorKey]);
        $cart->user_id = $userId ?: $cart->user_id;
        $cart->email = $email ?: $cart->email;
        $cart->customer_name = $name ?: $cart->customer_name;
        $cart->items = array_values($items);
        $cart->cart_total = collect($items)->sum(fn ($item) => (float) ($item['price'] ?? 0) * max(1, (int) ($item['qty'] ?? 1)));
        $cart->first_added_at ??= now();
        $cart->last_activity_at = now();
        $cart->purchased_at = null;
        $cart->stage_one_sent_at = null;
        $cart->stage_two_sent_at = null;
        $cart->stage_three_sent_at = null;
        $cart->save();
        return $cart;
    }

    private function frontendUrl(): string
    {
        return rtrim((string) (config('app.url') ?: env('APP_URL', 'https://www.weofferwellness.co.uk')), '/');
    }

    public function run(): array
    {
        $sent = 0; $skipped = 0;
        foreach (StoreAbandonedCart::query()->whereNull('purchased_at')->whereNotNull('email')->cursor() as $cart) {
            $this->markPurchasedIfNeeded($cart);
            if ($cart->purchased_at || !$cart->email || !$cart->items) { $skipped++; continue; }
            $stage = $this->dueStage($cart);
            if (!$stage) { $skipped++; continue; }
            try {
                $items = collect($cart->items)->map(fn ($item) => array_merge((array) $item, [
                    'title' => $item['title'] ?? 'Wellness offering',
                    'provider' => data_get($item, 'meta.practitioner', data_get($item, 'provider', 'We Offer Wellness practitioner')),
                    'summary' => data_get($item, 'meta.summary', ''),
                    'imageUrl' => $item['image'] ?? $item['imageUrl'] ?? null,
                    'url' => $item['url'] ?? $this->frontendUrl().'/offerings',
                    'price' => '£'.number_format((float) ($item['price'] ?? 0), 2),
                ]))->values()->all();
                MailService::send($cart->email, $this->subject($stage), "emails.store.abandoned-cart-{$stage}", [
                    'customerName' => $cart->customer_name ?: 'there', 'items' => $items,
                    'cartTotal' => '£'.number_format((float) $cart->cart_total, 2),
                    'resumeUrl' => $this->frontendUrl().'/cart',
                ], null, 'We Offer Wellness', [
                    'source_type' => 'store-abandoned-cart', 'source_label' => 'Store abandoned cart workflow',
                    'tracking_source' => ['type' => 'store-abandoned-cart', 'id' => $cart->id],
                    'recipient_user_id' => $cart->user_id, 'recipient_name' => $cart->customer_name,
                ]);
                $cart->forceFill(["stage_{$stage}_sent_at" => now()])->save(); $sent++;
            } catch (\Throwable $e) { Log::error('store.abandoned_cart.send_failed', ['cart' => $cart->id, 'error' => $e->getMessage()]); $skipped++; }
        }
        return compact('sent', 'skipped');
    }

    private function dueStage(StoreAbandonedCart $cart): ?string
    {
        $age = Carbon::parse($cart->last_activity_at ?: $cart->first_added_at)->diffInHours(now());
        if (!$cart->stage_one_sent_at && $age >= 1) return 'one';
        if (!$cart->stage_two_sent_at && $age >= 24) return 'two';
        if (!$cart->stage_three_sent_at && $age >= 72) return 'three';
        return null;
    }

    private function markPurchasedIfNeeded(StoreAbandonedCart $cart): void
    {
        $query = Order::query()->whereIn('status', ['paid','completed','complete','confirmed'])->where('created_at', '>=', $cart->first_added_at ?: $cart->created_at);
        $query->where(function ($q) use ($cart) {
            if ($cart->email) $q->where('email', $cart->email);
            if ($cart->user_id) $q->orWhere('user_id', $cart->user_id);
        });
        if ($query->exists()) $cart->forceFill(['purchased_at' => now()])->save();
    }

    private function subject(string $stage): string { return ['one' => 'Still thinking it over?', 'two' => 'A little nudge for your wellbeing', 'three' => 'Your We Offer Wellness cart'][$stage]; }
}
