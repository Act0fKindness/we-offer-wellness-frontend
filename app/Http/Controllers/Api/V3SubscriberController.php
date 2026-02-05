<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V3Subscriber;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class V3SubscriberController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'landing_path' => 'nullable|string|max:2048',
            'referrer' => 'nullable|string',
            'session_token' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:100',
            'locale' => 'nullable|string|max:20',
            'languages' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:120',
            'user_agent' => 'nullable|string',
            'device_memory' => 'nullable|string|max:20',
            'hardware_concurrency' => 'nullable|string|max:20',
            'screen_width' => 'nullable|integer',
            'screen_height' => 'nullable|integer',
            'geo_lat' => 'nullable|numeric',
            'geo_lng' => 'nullable|numeric',
            'geo_accuracy' => 'nullable|numeric',
            'session_started_at' => 'nullable|date',
            'session_duration_seconds' => 'nullable|integer|min:0',
        ]);

        $token = $data['session_token'] ?? null;
        $landingPath = $data['landing_path'] ?? null;
        $referrer = $data['referrer'] ?? null;
        $sessionStarted = isset($data['session_started_at']) ? Carbon::parse($data['session_started_at']) : null;
        $duration = isset($data['session_duration_seconds']) ? max(0, (int) $data['session_duration_seconds']) : null;
        $meta = $this->extractMeta($data, $request);

        if ($token) {
            $tracked = V3Subscriber::where('session_token', $token)->first();
            if ($tracked) {
                $tracked->email = $data['email'];
                if (!empty($data['name'])) {
                    $tracked->name = $data['name'];
                }
                if ($landingPath && !$tracked->landing_path) {
                    $tracked->landing_path = $landingPath;
                }
                if ($referrer && !$tracked->referrer) {
                    $tracked->referrer = $referrer;
                }
                if ($sessionStarted && !$tracked->session_started_at) {
                    $tracked->session_started_at = $sessionStarted;
                }
                if ($duration !== null) {
                    $tracked->session_duration_seconds = max($tracked->session_duration_seconds ?? 0, $duration);
                }
                $tracked->last_seen_at = now();
                $tracked->fill($meta);
                $tracked->save();

                return response()->json(['ok' => true, 'session_token' => $tracked->session_token]);
            }
        }

        // De-duplicate by email + landing_path when no session token exists
        $existing = V3Subscriber::where('email', $data['email'])
            ->where('landing_path', $landingPath)
            ->first();
        if ($existing) {
            if (!empty($data['name'])) {
                $existing->name = $data['name'];
            }
            if ($referrer && !$existing->referrer) {
                $existing->referrer = $referrer;
            }
            if ($token) {
                $existing->session_token = $token;
            }
            if ($sessionStarted && !$existing->session_started_at) {
                $existing->session_started_at = $sessionStarted;
            }
            if ($duration !== null) {
                $existing->session_duration_seconds = max($existing->session_duration_seconds ?? 0, $duration);
            }
            $existing->last_seen_at = now();
            $existing->fill($meta);
            $existing->save();

            return response()->json(['ok' => true, 'session_token' => $existing->session_token]);
        }

        if (!$token) {
            $token = (string) Str::uuid();
        }

        $record = V3Subscriber::create(array_merge(array_filter([
            'session_token' => $token,
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
            'landing_path' => $landingPath,
            'referrer' => $referrer,
            'session_started_at' => $sessionStarted ?? now(),
            'last_seen_at' => now(),
            'session_duration_seconds' => $duration ?? 0,
        ], fn($value) => !is_null($value) && $value !== ''), $meta));

        return response()->json(['ok' => true, 'session_token' => $record['session_token'] ?? $token]);
    }

    public function track(Request $request)
    {
        $data = $request->validate([
            'landing_path' => 'nullable|string|max:2048',
            'referrer' => 'nullable|string',
            'session_token' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:100',
            'locale' => 'nullable|string|max:20',
            'languages' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:120',
            'user_agent' => 'nullable|string',
            'device_memory' => 'nullable|string|max:20',
            'hardware_concurrency' => 'nullable|string|max:20',
            'screen_width' => 'nullable|integer',
            'screen_height' => 'nullable|integer',
            'geo_lat' => 'nullable|numeric',
            'geo_lng' => 'nullable|numeric',
            'geo_accuracy' => 'nullable|numeric',
            'session_started_at' => 'nullable|date',
            'session_duration_seconds' => 'nullable|integer|min:0',
        ]);

        $token = $data['session_token'] ?? null;
        $landingPath = $data['landing_path'] ?? null;
        $referrer = $data['referrer'] ?? null;
        $sessionStarted = isset($data['session_started_at']) ? Carbon::parse($data['session_started_at']) : null;
        $duration = isset($data['session_duration_seconds']) ? max(0, (int) $data['session_duration_seconds']) : null;
        $meta = $this->extractMeta($data, $request);

        if ($token) {
            $existing = V3Subscriber::where('session_token', $token)->first();
            if ($existing) {
                $updated = false;
                if ($landingPath && !$existing->landing_path) {
                    $existing->landing_path = $landingPath;
                    $updated = true;
                }
                if ($referrer && !$existing->referrer) {
                    $existing->referrer = $referrer;
                    $updated = true;
                }
                if ($sessionStarted && !$existing->session_started_at) {
                    $existing->session_started_at = $sessionStarted;
                    $updated = true;
                }
                if ($duration !== null) {
                    $existing->session_duration_seconds = max($existing->session_duration_seconds ?? 0, $duration);
                    $updated = true;
                }
                $existing->last_seen_at = now();
                $existing->fill($meta);
                if ($updated || !empty($meta)) {
                    $existing->save();
                }

                return response()->json(['session_token' => $existing->session_token]);
            }
        }

        $token = (string) Str::uuid();
        V3Subscriber::create(array_merge(array_filter([
            'session_token' => $token,
            'landing_path' => $landingPath,
            'referrer' => $referrer,
            'session_started_at' => $sessionStarted ?? now(),
            'last_seen_at' => now(),
            'session_duration_seconds' => $duration ?? 0,
        ], fn($value) => !is_null($value) && $value !== ''), $meta));

        return response()->json(['session_token' => $token]);
    }

    protected function extractMeta(array $data, Request $request): array
    {
        $meta = [
            'timezone' => $data['timezone'] ?? null,
            'locale' => $data['locale'] ?? null,
            'languages' => $data['languages'] ?? null,
            'platform' => $data['platform'] ?? null,
            'user_agent' => $data['user_agent'] ?? $request->userAgent(),
            'device_memory' => $data['device_memory'] ?? null,
            'hardware_concurrency' => $data['hardware_concurrency'] ?? null,
            'screen_width' => $data['screen_width'] ?? null,
            'screen_height' => $data['screen_height'] ?? null,
            'geo_lat' => $data['geo_lat'] ?? null,
            'geo_lng' => $data['geo_lng'] ?? null,
            'geo_accuracy' => $data['geo_accuracy'] ?? null,
        ];

        return array_filter($meta, fn ($value) => !is_null($value) && $value !== '');
    }
}
