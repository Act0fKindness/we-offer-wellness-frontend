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

        $seed = trim((string) config('app.key', '')) . '|' . trim((string) config('app.url', url('/')));
        $key = substr(hash('sha256', $seed), 0, 32);

        return Str::lower($key);
    }

    public function host(): string
    {
        $host = parse_url((string) config('app.url', url('/')), PHP_URL_HOST);

        return strtolower(trim((string) ($host ?: request()->getHost())));
    }

    public function keyLocation(): string
    {
        return url('/indexnow.txt');
    }

    public function endpoint(): string
    {
        return 'https://api.indexnow.org/indexnow';
    }
}
