<?php

namespace App\Services;

use App\Models\CheckoutAttempt;
use App\Models\Order;
use App\Models\User;
use App\Models\V3Subscriber;

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
