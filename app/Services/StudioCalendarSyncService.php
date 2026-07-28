<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StudioCalendarSyncService
{
    public function syncBookingIds(array $bookingIds): void
    {
        $url = trim((string) config('services.studio_calendar_sync.url'));
        $secret = trim((string) config('services.studio_calendar_sync.secret'));
        $ids = collect($bookingIds)
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($url === '' || $secret === '' || $ids === []) {
            return;
        }

        $payload = ['booking_ids' => $ids];
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-WOW-Calendar-Signature' => hash_hmac('sha256', $body, $secret),
                ])
                ->withBody($body, 'application/json')
                ->post($url);

            if ($response->failed()) {
                Log::warning('studio.calendar_sync.failed', [
                    'status' => $response->status(),
                    'booking_ids' => $ids,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('studio.calendar_sync.exception', [
                'booking_ids' => $ids,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
