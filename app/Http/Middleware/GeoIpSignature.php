<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GeoIpSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $ip = $request->ip();
        $sig = $this->ipSig($ip);
        $prev = (string) $request->cookie('wow_ip_sig', '');

        if ($sig !== '' && $prev !== '' && $sig !== $prev) {
            // Flag to re-ask user to confirm/update location on next render
            $response->headers->setCookie(cookie('wow_geo_reask', '1', 60*24));
        }
        // Always persist the current signature for future comparison
        $response->headers->setCookie(cookie('wow_ip_sig', $sig, 60*24*365*5));

        return $response;
    }

    private function ipSig(?string $ip): string
    {
        if (!$ip) return '';
        if (strpos($ip, ':') !== false) {
            // IPv6 — take first 3 hextets
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 3));
        }
        // IPv4 — take first two octets
        $parts = explode('.', $ip);
        if (count($parts) < 2) return $ip;
        return $parts[0].'.'.$parts[1];
    }
}

