<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SubmitSearchConsoleSitemap extends Command
{
    protected $signature = 'search-console:submit-sitemap
        {--property= : Search Console property URL or sc-domain property}
        {--sitemap= : Sitemap URL to submit}
        {--sitemaps= : Comma-separated sitemap URLs to submit}
        {--client-id= : Google OAuth client ID}
        {--client-secret= : Google OAuth client secret}
        {--refresh-token= : Google OAuth refresh token}';

    protected $description = 'Submit the public sitemap to Google Search Console via the Sitemaps API using OAuth.';

    public function handle(): int
    {
        try {
            $propertyUrl = trim((string) ($this->option('property') ?: config('services.search_console.property_url', '')));
            $sitemapUrls = $this->resolveSitemapUrls();
            $credentials = $this->loadOAuthCredentials();

            if ($propertyUrl === '') {
                $this->error('Missing Search Console property URL. Set GOOGLE_SEARCH_CONSOLE_PROPERTY_URL.');
                return self::FAILURE;
            }

            if ($sitemapUrls === []) {
                $this->error('Missing sitemap URL(s). Set GOOGLE_SEARCH_CONSOLE_SITEMAP_URLS / GOOGLE_SEARCH_CONSOLE_SITEMAP_URL or pass --sitemaps=.');
                return self::FAILURE;
            }

            if ($credentials === null) {
                $this->error('Missing Google OAuth credentials. Set GOOGLE_SEARCH_CONSOLE_CLIENT_ID, GOOGLE_SEARCH_CONSOLE_CLIENT_SECRET, and GOOGLE_SEARCH_CONSOLE_REFRESH_TOKEN.');
                $this->line('Use `php artisan search-console:oauth-url` and `php artisan search-console:oauth-exchange {code}` to create a refresh token.');
                return self::FAILURE;
            }

            [$clientId, $clientSecret, $refreshToken] = $credentials;
            $token = $this->fetchAccessToken($clientId, $clientSecret, $refreshToken);
            $success = true;

            foreach ($sitemapUrls as $sitemapUrl) {
                $this->verifySitemapReachable($sitemapUrl);

                $response = $this->submitSitemap($propertyUrl, $sitemapUrl, $token);

                if (!$response->successful()) {
                    $success = false;
                    $this->error("Search Console rejected the sitemap submission for {$sitemapUrl}.");
                    $this->error($this->describeResponse($response));
                    continue;
                }

                $this->info("Submitted {$sitemapUrl} to Search Console for {$propertyUrl}.");
            }

            return $success ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * @return array<int, string>
     */
    private function resolveSitemapUrls(): array
    {
        $raw = trim((string) ($this->option('sitemaps') ?: config('services.search_console.sitemap_urls', '')));

        if ($raw === '') {
            $fallback = trim((string) ($this->option('sitemap') ?: config('services.search_console.sitemap_url', '')));
            $raw = $fallback;
        }

        if ($raw === '') {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn (string $url): string => trim($url))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{0:string,1:string,2:string}|null
     */
    private function loadOAuthCredentials(): ?array
    {
        $clientId = trim((string) ($this->option('client-id') ?: config('services.search_console.client_id', '')));
        $clientSecret = trim((string) ($this->option('client-secret') ?: config('services.search_console.client_secret', '')));
        $refreshToken = trim((string) ($this->option('refresh-token') ?: config('services.search_console.refresh_token', '')));
        $credentialsFile = trim((string) config('services.search_console.client_credentials_file', ''));

        if (($clientId === '' || $clientSecret === '') && $credentialsFile !== '' && File::isFile($credentialsFile)) {
            $data = json_decode((string) File::get($credentialsFile), true);

            if (is_array($data)) {
                $clientId = $clientId !== '' ? $clientId : trim((string) ($data['web']['client_id'] ?? $data['installed']['client_id'] ?? ''));
                $clientSecret = $clientSecret !== '' ? $clientSecret : trim((string) ($data['web']['client_secret'] ?? $data['installed']['client_secret'] ?? ''));
            }
        }

        if ($clientId === '' || $clientSecret === '' || $refreshToken === '') {
            return null;
        }

        return [$clientId, $clientSecret, $refreshToken];
    }

    private function verifySitemapReachable(string $sitemapUrl): void
    {
        $response = Http::timeout(20)->retry(2, 250)->accept('application/xml')->get($sitemapUrl);

        if (!$response->successful()) {
            throw new \RuntimeException("The sitemap URL is not reachable: {$sitemapUrl}. " . $this->describeResponse($response));
        }
    }

    private function fetchAccessToken(string $clientId, string $clientSecret, string $refreshToken): string
    {
        $response = Http::asForm()
            ->timeout(20)
            ->retry(2, 250)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

        if (!$response->successful() || empty($response->json('access_token'))) {
            throw new \RuntimeException('Unable to obtain a Google OAuth access token. ' . $this->describeResponse($response));
        }

        return (string) $response->json('access_token');
    }

    private function submitSitemap(string $propertyUrl, string $sitemapUrl, string $token): Response
    {
        $endpoint = sprintf(
            'https://www.googleapis.com/webmasters/v3/sites/%s/sitemaps/%s',
            rawurlencode($propertyUrl),
            rawurlencode($sitemapUrl),
        );

        return Http::withToken($token)
            ->acceptJson()
            ->withBody('', 'text/plain')
            ->timeout(20)
            ->retry(2, 250)
            ->send('PUT', $endpoint);
    }

    private function describeResponse(Response $response): string
    {
        $body = trim((string) $response->body());

        if ($body === '') {
            return sprintf('HTTP %d with an empty response body.', $response->status());
        }

        return sprintf('HTTP %d: %s', $response->status(), Str::limit($body, 500));
    }
}
