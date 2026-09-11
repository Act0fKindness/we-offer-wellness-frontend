<?php

namespace App\Services;

use Illuminate\Support\Str;

class IndexNowService
{
    public function key(): string
    {
        $configured = trim((string) config('services.indexnow.key', ''));
        if ($configured !== '') {
            return Str::lower($configured);
        }

        $seed = trim((string) config('app.key', '')) . '|' . trim((string) config('services.public_site_url', 'https://www.weofferwellness.co.uk'));
        $key = substr(hash('sha256', $seed), 0, 32);

        return Str::lower($key);
    }

    public function host(): string
    {
        $host = parse_url((string) config('services.public_site_url', 'https://www.weofferwellness.co.uk'), PHP_URL_HOST);

        return strtolower(trim((string) ($host ?: 'www.weofferwellness.co.uk')));
    }

    public function keyLocation(): string
    {
        return rtrim((string) config('services.public_site_url', 'https://www.weofferwellness.co.uk'), '/') . '/indexnow.txt';
    }

    public function endpoint(): string
    {
        return 'https://api.indexnow.org/indexnow';
    }
}
