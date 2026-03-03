<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MailService
{
    /**
     * Send an email using the Brevo (Sendinblue) API.
     *
     * @param string $to Recipient email address
     * @param string $subject Subject line
     * @param string $view Blade view path for HTML
     * @param array $data Data passed to the view
     * @param string|null $from Optional from email override
     * @param string|null $fromName Optional from name override
     */
    public static function send(string $to, string $subject, string $view, array $data = [], ?string $from = null, ?string $fromName = null, array $options = []): array
    {
        // Read via configuration so it works with cached config in production
        $apiKey = (string) (config('services.brevo.key') ?: env('BREVO_API_KEY'));
        if (!$apiKey) {
            throw new \RuntimeException('BREVO_API_KEY not configured');
        }

        $html = view($view, $data)->render();
        $plain = strip_tags($html);

        $fromEmail = $from ?? config('mail.from.address');
        $fromName  = $fromName ?? config('mail.from.name');

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
            'tags' => ['transactional','verification','atease'],
            'headers' => [
                'X-Mailin-custom' => 'transactional-verification',
                'X-Priority' => '3',
            ],
        ];

        if (!empty($options['reply_to'])) {
            $reply = $options['reply_to'];
            $payload['replyTo'] = [
                'email' => (string) ($reply['email'] ?? $fromEmail),
                'name' => (string) ($reply['name'] ?? $fromName),
            ];
        }

        foreach (['bcc', 'cc'] as $type) {
            if (empty($options[$type]) || !is_array($options[$type])) {
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
            if (!empty($list)) {
                $payload[$type] = $list;
            }
        }

        if (!empty($options['tags']) && is_array($options['tags'])) {
            $payload['tags'] = array_values(array_unique(array_filter(array_map('strval', $options['tags']))));
        }

        if (!empty($options['headers']) && is_array($options['headers'])) {
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
}
