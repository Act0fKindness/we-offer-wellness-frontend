<?php

namespace App\Services;

use App\Models\CheckoutAttempt;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\V3Subscriber;
use App\Models\VendorDetail;

class TransactionalMail
{
    public static function subscriberConfirm(V3Subscriber $subscriber): void
    {
        if (!$subscriber->email || !$subscriber->confirmation_token) {
            return;
        }

        $confirmUrl = route('subscribe.confirm', ['token' => $subscriber->confirmation_token]);
        $manageUrl = self::preferencesUrl($subscriber);

        MailService::send(
            $subscriber->email,
            'Confirm your We Offer Wellness subscription',
            'emails.subscriber-confirm',
            [
                'subscriber' => $subscriber,
                'confirmUrl' => $confirmUrl,
                'manageUrl' => $manageUrl,
            ],
            null,
            null,
            ['tags' => ['marketing', 'opt-in', 'wow-subscribe']]
        );
    }

    public static function subscriberWelcome(V3Subscriber $subscriber): void
    {
        if (!$subscriber->email) {
            return;
        }

        MailService::send(
            $subscriber->email,
            'Welcome to We Offer Wellness',
            'emails.subscriber-welcome',
            [
                'subscriber' => $subscriber,
                'preferencesUrl' => self::preferencesUrl($subscriber),
                'browseUrl' => url('/online'),
                'nearbyUrl' => url('/near-me'),
            ],
            null,
            null,
            ['tags' => ['marketing', 'welcome', 'wow-subscribe']]
        );
    }

    public static function subscriberPreferencePrompt(V3Subscriber $subscriber): void
    {
        if (!$subscriber->email) {
            return;
        }

        MailService::send(
            $subscriber->email,
            'Set your preferences',
            'emails.subscriber-preferences',
            [
                'subscriber' => $subscriber,
                'preferencesUrl' => self::preferencesUrl($subscriber),
            ],
            null,
            null,
            ['tags' => ['marketing', 'preference', 'wow-subscribe']]
        );
    }

    public static function subscriberPreferencesUpdated(V3Subscriber $subscriber): void
    {
        if (!$subscriber->email) {
            return;
        }

        MailService::send(
            $subscriber->email,
            'Preferences updated',
            'emails.subscriber-preferences-updated',
            [
                'subscriber' => $subscriber,
                'preferences' => $subscriber->preferences ?? [],
                'preferencesUrl' => self::preferencesUrl($subscriber),
            ],
            null,
            null,
            ['tags' => ['marketing', 'preference', 'wow-subscribe']]
        );
    }

    public static function subscriberUnsubscribed(V3Subscriber $subscriber): void
    {
        if (!$subscriber->email) {
            return;
        }

        MailService::send(
            $subscriber->email,
            'You are unsubscribed',
            'emails.subscriber-unsubscribed',
            [
                'subscriber' => $subscriber,
                'resubscribeUrl' => self::resubscribeUrl($subscriber),
            ],
            null,
            null,
            ['tags' => ['marketing', 'unsubscribe', 'wow-subscribe']]
        );
    }

    public static function subscriberResubscribed(V3Subscriber $subscriber): void
    {
        if (!$subscriber->email) {
            return;
        }

        MailService::send(
            $subscriber->email,
            'Welcome back to We Offer Wellness',
            'emails.subscriber-resubscribed',
            [
                'subscriber' => $subscriber,
                'preferencesUrl' => self::preferencesUrl($subscriber),
            ],
            null,
            null,
            ['tags' => ['marketing', 'resubscribe', 'wow-subscribe']]
        );
    }

    public static function subscriberPractitionerInterest(V3Subscriber $subscriber): void
    {
        if (!$subscriber->email) {
            return;
        }

        MailService::send(
            $subscriber->email,
            'Thanks for your interest in becoming a WOW Practitioner',
            'emails.subscriber-practitioner-interest',
            [
                'subscriber' => $subscriber,
                'providersUrl' => url('/providers'),
                'helpUrl' => url('/help'),
            ],
            null,
            null,
            ['tags' => ['marketing', 'practitioner', 'wow-subscribe']]
        );
    }

    public static function accountWelcome(User $user): void
    {
        if (!$user->email) {
            return;
        }

        MailService::send(
            $user->email,
            'Welcome to We Offer Wellness',
            'emails.account-welcome',
            [
                'user' => $user,
                'dashboardUrl' => url('/account'),
                'helpUrl' => url('/help'),
            ],
            null,
            null,
            ['tags' => ['account', 'welcome', 'wow']] 
        );
    }

    public static function passwordChanged(User $user, array $context = []): void
    {
        if (!$user->email) {
            return;
        }

        MailService::send(
            $user->email,
            'Your We Offer Wellness password was updated',
            'emails.password-changed',
            [
                'user' => $user,
                'context' => $context,
            ],
            null,
            null,
            ['tags' => ['account', 'security']]
        );
    }

    public static function emailChanged(User $user, string $oldEmail): void
    {
        $newEmail = $user->email;
        if ($newEmail) {
            MailService::send(
                $newEmail,
                'Your email was updated',
                'emails.email-changed',
                [
                    'user' => $user,
                    'scope' => 'new',
                    'oldEmail' => $oldEmail,
                ],
                null,
                null,
                ['tags' => ['account', 'security']]
            );
        }

        if ($oldEmail && $oldEmail !== $newEmail) {
            MailService::send(
                $oldEmail,
                'Your We Offer Wellness email changed',
                'emails.email-changed',
                [
                    'user' => $user,
                    'scope' => 'old',
                    'oldEmail' => $oldEmail,
                ],
                null,
                null,
                ['tags' => ['account', 'security']]
            );
        }
    }

    public static function loginAlert(User $user, array $context = []): void
    {
        if (!$user->email) {
            return;
        }

        MailService::send(
            $user->email,
            'New login to your We Offer Wellness account',
            'emails.login-alert',
            [
                'user' => $user,
                'context' => $context,
            ],
            null,
            null,
            ['tags' => ['account', 'security']]
        );
    }

    public static function accountDeleted(?string $email, ?string $name = null): void
    {
        if (!$email) {
            return;
        }

        MailService::send(
            $email,
            'Your We Offer Wellness account is closed',
            'emails.account-deleted',
            [
                'name' => $name,
                'supportUrl' => url('/help'),
            ],
            null,
            null,
            ['tags' => ['account', 'closure']]
        );
    }

    public static function orderReceipt(Order $order): void
    {
        $order->loadMissing('items');
        $email = $order->email;
        if (!$email) {
            return;
        }

        MailService::send(
            $email,
            'Order #'.$order->id.' confirmed',
            'emails.order-confirmation',
            [
                'order' => $order,
                'supportUrl' => url('/help'),
            ],
            null,
            null,
            ['tags' => ['order', 'receipt']]
        );
    }

    public static function vendorOrderNotification(Order $order): bool
    {
        $groups = self::vendorGroupsForOrder($order);
        if (empty($groups)) {
            return false;
        }

        $sent = false;
        foreach ($groups as $group) {
            $email = $group['email'] ?? null;
            if (!$email) {
                continue;
            }
            $items = self::itemsForVendor($group);
            if (empty($items)) {
                continue;
            }
            MailService::send(
                $email,
                'New We Offer Wellness booking (Order #'.$order->id.')',
                'emails.vendor-order-notification',
                [
                    'order' => $order,
                    'vendor' => $group['vendor'],
                    'items' => $items,
                    'customerEmail' => $order->email,
                ],
                null,
                null,
                ['tags' => ['order', 'vendor', 'booking']]
            );
            $sent = true;
        }

        return $sent;
    }

    public static function vendorIntroduction(Order $order): bool
    {
        $customerEmail = $order->email;
        if (!$customerEmail) {
            return false;
        }

        $groups = self::vendorGroupsForOrder($order);
        if (empty($groups)) {
            return false;
        }

        $supportEmail = self::conciergeEmail();
        $customerName = self::customerName($order);

        $sent = false;
        foreach ($groups as $group) {
            $email = $group['email'] ?? null;
            if (!$email) {
                continue;
            }
            $items = self::itemsForVendor($group);
            if (empty($items)) {
                continue;
            }

            $cc = array_values(array_filter([
                ['email' => $customerEmail, 'name' => $customerName ?: $customerEmail],
                $supportEmail ? ['email' => $supportEmail, 'name' => 'We Offer Wellness'] : null,
            ], function (?array $entry) {
                return !empty($entry['email']);
            }));

            $options = [
                'tags' => ['order', 'introduction'],
                'cc' => $cc,
            ];

            if ($supportEmail) {
                $options['reply_to'] = [
                    'email' => $supportEmail,
                    'name' => 'We Offer Wellness',
                ];
            }

            MailService::send(
                $email,
                'Booking introduction for Order #'.$order->id,
                'emails.order-introduction',
                [
                'order' => $order,
                'vendor' => $group['vendor'],
                'items' => $items,
                'customerEmail' => $customerEmail,
                'customerName' => $customerName,
                'supportEmail' => $supportEmail,
            ],
                null,
                null,
                $options
            );
            $sent = true;
        }

        return $sent;
    }

    public static function paymentFailed(?CheckoutAttempt $attempt = null, ?Order $order = null, array $context = []): void
    {
        $order?->loadMissing('items');
        $email = $order->email ?? $attempt->email ?? null;
        if (!$email) {
            return;
        }

        MailService::send(
            $email,
            'We couldn’t complete your payment',
            'emails.payment-failed',
            [
                'order' => $order,
                'attempt' => $attempt,
                'items' => self::emailItems($order, $attempt),
                'context' => $context,
            ],
            null,
            null,
            ['tags' => ['order', 'payment-failed']]
        );
    }

    public static function paymentCancelled(?CheckoutAttempt $attempt = null, ?Order $order = null, int $holdHours = 48): void
    {
        $order?->loadMissing('items');
        $email = $attempt->email ?? $order->email ?? null;
        if (!$email) {
            return;
        }

        MailService::send(
            $email,
            'We saved your We Offer Wellness cart',
            'emails.payment-cancelled',
            [
                'items' => self::emailItems($order, $attempt),
                'resumeUrl' => url('/cart'),
                'holdHours' => $holdHours,
            ],
            null,
            null,
            ['tags' => ['order', 'abandoned']]
        );
    }

    public static function refundNotification(Order $order, array $context = []): void
    {
        $order->loadMissing('items');
        $email = $order->email;
        if (!$email) {
            return;
        }

        MailService::send(
            $email,
            'Your refund is on the way',
            'emails.refund-confirmation',
            [
                'order' => $order,
                'context' => $context,
            ],
            null,
            null,
            ['tags' => ['order', 'refund']]
        );
    }

    public static function disputeOpened(Order $order, array $context = []): void
    {
        $order->loadMissing('items');
        $email = $order->email;
        if (!$email) {
            return;
        }

        MailService::send(
            $email,
            'Heads up: payment dispute received',
            'emails.dispute-notice',
            [
                'order' => $order,
                'context' => $context,
            ],
            null,
            null,
            ['tags' => ['order', 'dispute']]
        );
    }

    protected static function vendorGroupsForOrder(Order $order): array
    {
        $order->loadMissing('items');
        $items = $order->items;
        if ($items->isEmpty()) {
            return [];
        }

        $vendorIds = $items->pluck('vendor_id')->filter()->unique()->values();
        $productIds = $items->map(function (OrderItem $item) {
            return self::extractProductId($item);
        })->filter()->unique()->values();

        $vendors = $vendorIds->isEmpty()
            ? collect()
            : VendorDetail::with('user')->whereIn('id', $vendorIds)->get()->keyBy('id');

        $products = $productIds->isEmpty()
            ? collect()
            : Product::with('vendor.user')->whereIn('id', $productIds)->get()->keyBy('id');

        $groups = [];
        foreach ($items as $item) {
            $vendor = null;
            if ($item->vendor_id && $vendors->has($item->vendor_id)) {
                $vendor = $vendors->get($item->vendor_id);
            } else {
                $productId = self::extractProductId($item);
                if ($productId && $products->has($productId)) {
                    $vendor = optional($products->get($productId))->vendor;
                }
            }

            if (!$vendor) {
                continue;
            }

            $email = self::resolveVendorEmail($vendor);
            if (!$email) {
                continue;
            }

            $key = (string) $vendor->id;
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'vendor' => $vendor,
                    'email' => $email,
                    'items' => [],
                ];
            }

            $groups[$key]['items'][] = self::formatVendorItem($item, $vendor, $productId);
        }

        return array_values($groups);
    }

    protected static function formatVendorItem(OrderItem $item, ?VendorDetail $resolvedVendor = null, ?int $productId = null): array
    {
        $meta = is_array($item->meta) ? $item->meta : [];
        $productId = $productId ?? self::extractProductId($item);
        return [
            'name' => $item->name,
            'quantity' => (int) $item->quantity,
            'unit_amount' => (int) $item->unit_amount,
            'variant' => $meta['variant_label'] ?? null,
            'url' => $meta['url'] ?? null,
            'image' => $meta['image'] ?? null,
            'vendor_id' => (int) ($resolvedVendor->id ?? $item->vendor_id ?? $meta['vendor_id'] ?? 0) ?: null,
            'product_id' => $productId,
        ];
    }

    protected static function itemsForVendor(array $group): array
    {
        $vendorId = (int) ($group['vendor']->id ?? 0);
        if ($vendorId === 0) {
            return [];
        }

        return array_values(array_filter($group['items'] ?? [], function ($item) use ($vendorId) {
            return (int) ($item['vendor_id'] ?? 0) === $vendorId;
        }));
    }

    protected static function extractProductId(OrderItem $item): ?int
    {
        if ($item->product_id) {
            return (int) $item->product_id;
        }

        $meta = is_array($item->meta) ? $item->meta : [];
        $raw = $meta['product_id'] ?? null;
        if (is_numeric($raw)) {
            return (int) $raw;
        }
        $url = $meta['url'] ?? null;
        if (is_string($url) && preg_match('/\/(\d+)-/', $url, $matches)) {
            return (int) $matches[1];
        }
        if ($item->sku && preg_match('/(\d+)/', (string) $item->sku, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    protected static function resolveVendorEmail(?VendorDetail $vendor): ?string
    {
        if (!$vendor) {
            return null;
        }
        $raw = trim((string) ($vendor->user?->email ?: $vendor->vendor_contact));
        if ($raw === '') {
            return null;
        }
        if (str_contains($raw, '<') && preg_match('/<([^>]+)>/', $raw, $matches)) {
            $raw = trim($matches[1]);
        }
        return filter_var($raw, FILTER_VALIDATE_EMAIL) ? $raw : null;
    }

    protected static function conciergeEmail(): ?string
    {
        $email = config('mail.concierge_address') ?: 'hello@weofferwellness.co.uk';
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    protected static function customerName(Order $order): ?string
    {
        $order->loadMissing('customerProfile');
        $profile = $order->customerProfile;
        if (!$profile) {
            return null;
        }
        $name = trim(implode(' ', array_filter([$profile->first_name, $profile->last_name], function ($value) {
            return is_string($value) && trim($value) !== '';
        })));
        return $name !== '' ? $name : null;
    }

    protected static function preferencesUrl(V3Subscriber $subscriber): string
    {
        if (!$subscriber->manage_token) {
            return url('/');
        }

        return route('subscribe.preferences', ['token' => $subscriber->manage_token]);
    }

    protected static function resubscribeUrl(V3Subscriber $subscriber): string
    {
        if (!$subscriber->manage_token) {
            return url('/');
        }

        return route('subscribe.resubscribe', ['token' => $subscriber->manage_token]);
    }

    protected static function emailItems(?Order $order, ?CheckoutAttempt $attempt): array
    {
        if ($order && $order->relationLoaded('items')) {
            return $order->items->map(function ($item) {
                return [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'amount' => $item->unit_amount,
                ];
            })->all();
        }

        if ($attempt && is_array($attempt->items)) {
            $items = [];
            foreach ($attempt->items as $id => $raw) {
                $qty = max(1, (int) ($raw['qty'] ?? 1));
                $price = (float) ($raw['price'] ?? 0);
                $items[] = [
                    'name' => (string) ($raw['title'] ?? ('Item '.$id)),
                    'quantity' => $qty,
                    'amount' => $price >= 1000 ? (int) round($price) : (int) round($price * 100),
                ];
            }
            return $items;
        }

        return [];
    }
}
