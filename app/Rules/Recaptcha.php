<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! config('recaptcha.enabled')) {
            return;
        }

        $secret = config('recaptcha.secret_key');
        if (! $secret) {
            Log::warning('reCAPTCHA enabled but secret key is missing.');
            $fail('Security check is temporarily unavailable. Please contact support.');
            return;
        }

        if (! $value) {
            $fail('Please confirm you are not a robot.');
            return;
        }

        try {
            $response = Http::asForm()
                ->timeout((int) config('recaptcha.timeout', 5))
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);
        } catch (Throwable $exception) {
            Log::warning('reCAPTCHA verification failed to reach Google.', [
                'message' => $exception->getMessage(),
            ]);
            $fail('Security check could not be completed. Please try again.');
            return;
        }

        if (! $response->ok()) {
            Log::warning('reCAPTCHA verification response not OK.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $fail('Security check could not be completed. Please try again.');
            return;
        }

        $payload = $response->json();
        if (! data_get($payload, 'success')) {
            Log::info('reCAPTCHA verification failed.', [
                'errors' => data_get($payload, 'error-codes', []),
            ]);
            $fail('Security check failed. Please try again.');
        }
    }
}
