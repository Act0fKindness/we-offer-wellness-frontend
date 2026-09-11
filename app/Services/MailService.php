<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class MailService
{
    /**
     * Send a frontend transactional email through the backend relay when configured.
     *
     * In local/test environments the legacy Brevo path remains available as a fallback
     * when the relay URL/token are not configured.
     */
    public static function send(string $to, string $subject, string $view, array $data = [], ?string $from = null, ?string $fromName = null, array $options = []): array
    {
        $relayUrl = trim((string) config('services.mail_relay.url'));
        $relayToken = trim((string) config('services.mail_relay.token'));

        if ($relayUrl !== '' && $relayToken !== '') {
            try {
                return self::sendViaRelay($to, $subject, $view, $data, $from, $fromName, $options, $relayUrl, $relayToken);
            } catch (\Throwable $e) {
                logger()->error('Frontend mail relay failed', [
                    'to' => $to,
                    'subject' => $subject,
                    'view' => $view,
                    'error' => $e->getMessage(),
                ]);

                if (! app()->environment(['local', 'testing'])) {
                    throw $e;
                }
            }
        }

        if (app()->environment('production')) {
            throw new \RuntimeException('Frontend mail relay is not configured.');
        }

        return self::sendViaBrevoRendered($to, $subject, $view, $data, $from, $fromName, $options);
    }

    /**
     * Legacy raw HTML send. Kept for compatibility with older call sites.
     */
    public static function sendHtml(string $to, string $subject, string $html, array $options = [], ?string $from = null, ?string $fromName = null): array
    {
        return self::sendViaBrevoHtml($to, $subject, $html, $options, $from, $fromName);
    }

    private static function sendViaRelay(
        string $to,
        string $subject,
        string $view,
        array $data,
        ?string $from,
        ?string $fromName,
        array $options,
        string $relayUrl,
        string $relayToken
    ): array {
        $payload = [
            'to' => $to,
            'subject' => $subject,
            'view' => $view,
            'data' => self::normalizeForRelay($data),
            'from' => $from,
            'fromName' => $fromName,
            'options' => self::normalizeForRelay($options),
        ];

        try {
            $response = Http::timeout(20)
                ->retry(1, 250)
                ->withHeaders([
                    'X-WOW-Mail-Relay-Token' => $relayToken,
                    'Accept' => 'application/json',
                ])
                ->post($relayUrl, $payload);
        } catch (ConnectionException $e) {
            logger()->error('Frontend mail relay connection failed', [
                'to' => $to,
                'subject' => $subject,
                'view' => $view,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Unable to reach the backend mail relay.', 0, $e);
        }

        if ($response->failed()) {
            logger()->error('Frontend mail relay failed', [
                'to' => $to,
                'subject' => $subject,
                'view' => $view,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Unable to send email via backend relay.');
        }

        $json = $response->json() ?? [];
        logger()->info('Frontend email relayed via backend', [
            'to' => $to,
            'subject' => $subject,
            'view' => $view,
            'message_id' => $json['message_id'] ?? $json['messageId'] ?? null,
        ]);

        return is_array($json) ? $json : ['status' => 'sent', 'transport' => 'backend_relay'];
    }

    private static function sendViaBrevoRendered(
        string $to,
        string $subject,
        string $view,
        array $data = [],
        ?string $from = null,
        ?string $fromName = null,
        array $options = []
    ): array {
        $html = view($view, $data)->render();

        return self::sendViaBrevoHtml($to, $subject, $html, $options, $from, $fromName);
    }

    private static function sendViaBrevoHtml(
        string $to,
        string $subject,
        string $html,
        array $options = [],
        ?string $from = null,
        ?string $fromName = null
    ): array {
        $apiKey = (string) (config('services.brevo.key') ?: env('BREVO_API_KEY'));
        if ($apiKey === '') {
            throw new \RuntimeException('BREVO_API_KEY not configured');
        }

        $plain = strip_tags($html);
        $fromEmail = $from ?? config('mail.from.address');
        $isStore = str_starts_with((string) ($options['template_view'] ?? ''), 'emails.store.')
            || str_starts_with((string) ($options['source_type'] ?? data_get($options, 'tracking_source.type', '')), 'store-');
        $fromName = $isStore ? 'We Offer Wellness®' : 'WOW Studio by We Offer Wellness®';

        $payload = [
            'sender' => [
                'name' => (string) $fromName,
                'email' => (string) $fromEmail,
            ],
            'to' => [[
                'email' => (string) $to,
                'name' => explode('@', (string) $to)[0] ?? 'User',
            ]],
            'replyTo' => [
                'email' => (string) $fromEmail,
                'name' => (string) $fromName,
            ],
            'subject' => (string) $subject,
            'htmlContent' => $html,
            'textContent' => $plain,
            // Help inboxes classify as transactional + reduce spam
            'tags' => ['transactional', 'verification', 'atease'],
            'headers' => [
                'X-Mailin-custom' => 'transactional-verification',
                'X-Priority' => '3',
            ],
        ];

        if (! empty($options['reply_to'])) {
            $reply = $options['reply_to'];
            $payload['replyTo'] = [
                'email' => (string) ($reply['email'] ?? $fromEmail),
                'name' => (string) ($reply['name'] ?? $fromName),
            ];
        }

        foreach (['bcc', 'cc'] as $type) {
            if (empty($options[$type]) || ! is_array($options[$type])) {
                continue;
            }
            $list = [];
            foreach ($options[$type] as $entry) {
                if (empty($entry['email'])) {
                    continue;
                }
                $list[] = [
                    'email' => (string) $entry['email'],
                    'name' => (string) ($entry['name'] ?? ''),
                ];
            }
            if (! empty($list)) {
                $payload[$type] = $list;
            }
        }

        if (! empty($options['tags']) && is_array($options['tags'])) {
            $payload['tags'] = array_values(array_unique(array_filter(array_map('strval', $options['tags']))));
        }

        if (! empty($options['headers']) && is_array($options['headers'])) {
            $payload['headers'] = array_merge($payload['headers'], $options['headers']);
        }

        $response = Http::timeout(10)
            ->retry(1, 200)
            ->withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', $payload);

        if ($response->failed()) {
            logger()->error('Brevo API Email Error: '.$response->body(), ['payload' => $payload]);
            throw new \RuntimeException('Unable to send email via Brevo API');
        }

        $json = $response->json() ?? [];
        logger()->info('Brevo API Email Sent', ['to' => $to, 'subject' => $subject, 'id' => $json['messageId'] ?? null]);

        return $json;
    }

    private static function normalizeForRelay(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            return self::normalizeForRelay($value->toArray());
        }

        if ($value instanceof \JsonSerializable) {
            return self::normalizeForRelay($value->jsonSerialize());
        }

        if ($value instanceof \Traversable) {
            $array = [];
            foreach ($value as $key => $item) {
                $array[$key] = self::normalizeForRelay($item);
            }

            return $array;
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = self::normalizeForRelay($item);
            }

            return $normalized;
        }

        if (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                return self::normalizeForRelay($value->toArray());
            }

            return (array) $value;
        }

        return $value;
    }
}
