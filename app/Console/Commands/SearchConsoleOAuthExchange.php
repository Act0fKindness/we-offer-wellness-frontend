<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SearchConsoleOAuthExchange extends Command
{
    protected $signature = 'search-console:oauth-exchange
        {code : Authorization code returned by Google}
        {--client-id= : Google OAuth client ID}
        {--client-secret= : Google OAuth client secret}
        {--redirect-uri= : OAuth redirect URI used when generating the authorization URL}';

    protected $description = 'Exchange a Google OAuth authorization code for refresh token credentials.';

    public function handle(): int
    {
        $code = trim((string) $this->argument('code'));
        [$clientId, $clientSecret, $redirectUri] = $this->loadOAuthDetails();

        if ($code === '') {
            $this->error('Missing authorization code.');
            return self::FAILURE;
        }

        if ($clientId === '' || $clientSecret === '') {
            $this->error('Missing OAuth client credentials. Set GOOGLE_SEARCH_CONSOLE_CLIENT_ID and GOOGLE_SEARCH_CONSOLE_CLIENT_SECRET.');
            return self::FAILURE;
        }

        $response = Http::asForm()
            ->timeout(20)
            ->retry(2, 250)
            ->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

        if (!$response->successful()) {
            $this->error('Google rejected the authorization-code exchange.');
            $this->error($this->describeResponse($response));
            return self::FAILURE;
        }

        $refreshToken = trim((string) $response->json('refresh_token'));
        $accessToken = trim((string) $response->json('access_token'));
        $expiresIn = (int) $response->json('expires_in', 0);

        if ($refreshToken === '') {
            $this->warn('Google did not return a refresh token. Re-run the consent URL and make sure `prompt=consent` and `access_type=offline` are included.');
        } else {
            $this->line("GOOGLE_SEARCH_CONSOLE_REFRESH_TOKEN={$refreshToken}");
        }

        if ($accessToken !== '') {
            $this->line("Access token expires in {$expiresIn} seconds.");
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0:string,1:string,2:string}
     */
    private function loadOAuthDetails(): array
    {
        $clientId = trim((string) ($this->option('client-id') ?: config('services.search_console.client_id', '')));
        $clientSecret = trim((string) ($this->option('client-secret') ?: config('services.search_console.client_secret', '')));
        $redirectUri = trim((string) ($this->option('redirect-uri') ?: rtrim((string) config('app.url', ''), '/') . '/search-console/oauth/callback'));

        $credentialsFile = trim((string) config('services.search_console.client_credentials_file', ''));
        if (($clientId === '' || $clientSecret === '') && $credentialsFile !== '' && File::isFile($credentialsFile)) {
            $data = json_decode((string) File::get($credentialsFile), true);

            if (is_array($data)) {
                $clientId = $clientId !== '' ? $clientId : trim((string) ($data['web']['client_id'] ?? $data['installed']['client_id'] ?? ''));
                $clientSecret = $clientSecret !== '' ? $clientSecret : trim((string) ($data['web']['client_secret'] ?? $data['installed']['client_secret'] ?? ''));
            }
        }

        return [$clientId, $clientSecret, $redirectUri];
    }

    private function describeResponse($response): string
    {
        $body = trim((string) $response->body());

        if ($body === '') {
            return sprintf('HTTP %d with an empty response body.', $response->status());
        }

        return sprintf('HTTP %d: %s', $response->status(), Str::limit($body, 500));
    }
}
