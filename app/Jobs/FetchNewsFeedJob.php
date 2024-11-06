<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FetchNewsFeedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $baseUri;
    protected $authToken;

    public function __construct($baseUri = null)
    {
        $this->baseUri = $baseUri ?? env('NEWS_FEED_BASE_URI');
    }

    public function handle()
    {
        Log::info('FetchNewsFeedJob started.');

        $this->authenticate();

        if (!$this->authToken) {
            Log::error('auth token not available. Skipping news feed fetch.');
            return;
        }

        $response = Http::withHeaders($this->getAuthorizationHeader())
            ->get("{$this->baseUri}/v1/news");

        if ($response->successful()) {
            $newsFeed = $response->json();
            $result = app('meilisearch')->indexData('news_feed', $newsFeed);

            Log::info($result . ' News feed successfully indexed.');
        } else {
            Log::error('Failed to fetch news feed.', ['response' => $response->body()]);
        }
    }

    /**
     * Authenticate to retrieve the auth token.
     */
    protected function authenticate()
    {
        if (Cache::has('news_api_auth_token')) {
            $this->authToken = Cache::get('news_api_auth_token');
            return;
        }

        $response = Http::post("{$this->baseUri}/auth/login", [
            'email' => env('NEWS_API_EMAIL'),
            'password' => env('NEWS_API_PASSWORD'),
        ]);

        if ($response->successful()) {
            $this->authToken = $response->json()['token'] ?? null;
            $expiresIn = $response->json()['expires_in'] ?? 1000; // Default to 1000 seconds

            Cache::put('news_api_auth_token', $this->authToken, now()->addSeconds($expiresIn));

            Log::info('Authenticated and cached auth token with dynamic expiration.', [
                'expires_in' => $expiresIn,
            ]);
        } else {
            Log::error('Authentication failed.', ['response' => $response->body()]);
        }
    }

    /**
     * Get authorization headers using the auth token.
     *
     * @return array<string, string>
     */
    protected function getAuthorizationHeader(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->authToken,
        ];
    }
}
