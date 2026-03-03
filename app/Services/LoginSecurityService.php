<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginSecurityService
{
    public static function recordLogin(User $user, Request $request): void
    {
        $ip = $request->ip();
        $agent = (string) $request->userAgent();
        $fingerprint = hash('sha256', strtolower($agent ?: 'unknown').'|'.($ip ?: ''));

        $session = UserSession::where('user_id', $user->id)
            ->where('fingerprint', $fingerprint)
            ->first();

        if ($session) {
            $session->ip_address = $ip;
            $session->device = self::describeDevice($agent);
            $session->last_active_at = now();
            $session->save();
            return;
        }

        UserSession::create([
            'user_id' => $user->id,
            'fingerprint' => $fingerprint,
            'ip_address' => $ip,
            'device' => self::describeDevice($agent),
            'location' => self::approximateLocation($request),
            'last_active_at' => now(),
        ]);

        TransactionalMail::loginAlert($user, [
            'ip' => $ip,
            'device' => self::describeDevice($agent),
            'location' => self::approximateLocation($request),
            'user_agent' => $agent,
            'time' => now(),
        ]);
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
