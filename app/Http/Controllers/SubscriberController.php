<?php

namespace App\Http\Controllers;

use App\Models\V3Subscriber;
use App\Services\TransactionalMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class SubscriberController extends Controller
{
    public function confirm(string $token): View
    {
        $subscriber = V3Subscriber::where('confirmation_token', $token)->first();
        if (!$subscriber) {
            return $this->messageView('Link expired', 'That confirmation link is no longer valid. Tap subscribe on the site to get a fresh one.');
        }

        $wasConfirmed = (bool) $subscriber->confirmed_at;
        $subscriber->status = 'confirmed';
        $subscriber->confirmed_at = now();
        $subscriber->unsubscribed_at = null;
        $subscriber->confirmation_token = Str::random(64);
        if (!$subscriber->manage_token) {
            $subscriber->manage_token = Str::random(64);
        }
        $subscriber->save();

        if (!$wasConfirmed) {
            TransactionalMail::subscriberWelcome($subscriber);
            TransactionalMail::subscriberPreferencePrompt($subscriber);
        }

        return $this->messageView(
            'You’re confirmed',
            'Look out for curated rituals, invites, and new launches. Want to fine-tune recommendations?',
            'Update preferences',
            route('subscribe.preferences', ['token' => $subscriber->manage_token])
        );
    }

    public function preferences(string $token): View
    {
        $subscriber = V3Subscriber::where('manage_token', $token)->first();
        if (!$subscriber) {
            return $this->messageView('Link expired', 'That manage link is no longer valid. Opt in again from any on-site form.');
        }

        if ($subscriber->status === 'unsubscribed') {
            return $this->messageView(
                'You’re unsubscribed',
                'Want to come back? Tap below and we’ll restore your updates.',
                'Resubscribe',
                route('subscribe.resubscribe', ['token' => $subscriber->manage_token])
            );
        }

        return view('subscribers.preferences', [
            'subscriber' => $subscriber,
            'preferences' => $subscriber->preferences ?? [],
            'token' => $subscriber->manage_token,
        ]);
    }

    public function updatePreferences(Request $request, string $token): RedirectResponse
    {
        $subscriber = V3Subscriber::where('manage_token', $token)->firstOrFail();
        if ($subscriber->status === 'unsubscribed') {
            return redirect()->route('subscribe.preferences', ['token' => $token])->with('status', 'You’ll need to resubscribe first.');
        }

        $data = $request->validate([
            'interests' => ['nullable','array'],
            'interests.*' => ['in:online,in_person'],
            'location' => ['nullable','string','max:255'],
            'radius' => ['nullable','integer','min:5','max:250'],
            'goals' => ['nullable','array'],
            'goals.*' => ['string','max:120'],
        ]);

        $interests = $data['interests'] ?? [];
        $preferences = [
            'online' => in_array('online', $interests, true),
            'in_person' => in_array('in_person', $interests, true),
            'location' => $data['location'] ?? null,
            'radius' => $data['radius'] ?? null,
            'goals' => array_values(array_unique(array_filter($data['goals'] ?? []))),
        ];

        $subscriber->preferences = $preferences;
        $subscriber->save();

        TransactionalMail::subscriberPreferencesUpdated($subscriber);

        return redirect()->route('subscribe.preferences', ['token' => $token])->with('status', 'Preferences saved.');
    }

    public function unsubscribe(string $token): View
    {
        $subscriber = V3Subscriber::where('manage_token', $token)->first();
        if (!$subscriber) {
            return $this->messageView('Link expired', 'That unsubscribe link is no longer valid.');
        }

        if ($subscriber->status === 'unsubscribed') {
            return $this->messageView('Already unsubscribed', 'You’ll only hear from us again if you opt back in.');
        }

        $subscriber->status = 'unsubscribed';
        $subscriber->unsubscribed_at = now();
        $subscriber->confirmed_at = null;
        $subscriber->save();

        TransactionalMail::subscriberUnsubscribed($subscriber);

        return $this->messageView(
            'You’re unsubscribed',
            'Thanks for being with us. If you ever want rituals, offers, or invites again you can resubscribe below.',
            'Resubscribe',
            route('subscribe.resubscribe', ['token' => $subscriber->manage_token])
        );
    }

    public function resubscribe(string $token): View
    {
        $subscriber = V3Subscriber::where('manage_token', $token)->first();
        if (!$subscriber) {
            return $this->messageView('Link expired', 'That resubscribe link is no longer valid.');
        }

        $subscriber->status = 'confirmed';
        $subscriber->confirmed_at = now();
        $subscriber->unsubscribed_at = null;
        $subscriber->save();

        TransactionalMail::subscriberResubscribed($subscriber);

        return $this->messageView(
            'You’re back in',
            'Updates, drops, and launch news will resume. Fine-tune what lands in your inbox any time.',
            'Update preferences',
            route('subscribe.preferences', ['token' => $subscriber->manage_token])
        );
    }

    protected function messageView(string $title, string $body, ?string $cta = null, ?string $ctaUrl = null): View
    {
        return view('subscribers.message', [
            'title' => $title,
            'body' => $body,
            'cta' => $cta,
            'ctaUrl' => $ctaUrl,
        ]);
    }
}
