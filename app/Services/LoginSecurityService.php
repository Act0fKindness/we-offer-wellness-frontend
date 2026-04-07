<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LoginSecurityService
{
    public static function recordLogin(User $user, Request $request): void
    {
        if (!self::hasUserSessionsTable()) {
            return;
        }

        $ip = $request->ip();
        $agent = (string) $request->userAgent();
        $fingerprint = hash('sha256', strtolower($agent ?: 'unknown').'|'.($ip ?: ''));
        $device = self::describeDevice($agent);
        $hasFingerprint = self::hasFingerprintColumn();

        $sessionQuery = UserSession::where('user_id', $user->id);
        if ($hasFingerprint) {
            $sessionQuery->where('fingerprint', $fingerprint);
        } else {
            // Backward-compatible identity when legacy schemas lack fingerprint.
            $sessionQuery->where('ip_address', $ip)->where('device', $device);
        }
        $session = $sessionQuery->first();

        if ($session) {
            $session->ip_address = $ip;
            $session->device = $device;
            $session->last_active_at = now();
            $session->save();
            return;
        }

        $payload = [
            'user_id' => $user->id,
            'ip_address' => $ip,
            'device' => $device,
            'location' => self::approximateLocation($request),
            'last_active_at' => now(),
        ];
        if ($hasFingerprint) {
            $payload['fingerprint'] = $fingerprint;
        }
        UserSession::create($payload);

        TransactionalMail::loginAlert($user, [
            'ip' => $ip,
            'device' => $device,
            'location' => self::approximateLocation($request),
            'user_agent' => $agent,
            'time' => now(),
        ]);
    }

    protected static function hasUserSessionsTable(): bool
    {
        static $hasTable = null;
        if ($hasTable !== null) {
            return $hasTable;
        }

        try {
            $hasTable = Schema::hasTable((new UserSession())->getTable());
        } catch (\Throwable $e) {
            Log::warning('login_security.user_sessions_table_check_failed', ['e' => $e->getMessage()]);
            $hasTable = false;
        }

        return $hasTable;
    }

    protected static function hasFingerprintColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            $hasColumn = Schema::hasColumn((new UserSession())->getTable(), 'fingerprint');
        } catch (\Throwable $e) {
            Log::warning('login_security.fingerprint_column_check_failed', ['e' => $e->getMessage()]);
            $hasColumn = false;
        }

        return $hasColumn;
    }

    protected static function describeDevice(?string $userAgent): string
    {
        $ua = strtolower($userAgent ?? '');
        $device = 'Device unknown';
        if (Str::contains($ua, 'iphone')) {
            $device = 'iPhone';
        } elseif (Str::contains($ua, 'ipad')) {
            $device = 'iPad';
        } elseif (Str::contains($ua, 'android')) {
            $device = 'Android device';
        } elseif (Str::contains($ua, 'mac os')) {
            $device = 'Mac';
        } elseif (Str::contains($ua, 'windows')) {
            $device = 'Windows device';
        } elseif (Str::contains($ua, 'linux')) {
            $device = 'Linux device';
        }

        return $device;
    }

    protected static function approximateLocation(Request $request): ?string
    {
        $city = $request->header('CF-IPCity') ?: $request->header('X-Appengine-City');
        $country = $request->header('CF-IPCountry') ?: $request->header('X-Appengine-Country');
        $parts = array_filter([$city, $country]);
        return $parts ? implode(', ', $parts) : null;
    }
}
