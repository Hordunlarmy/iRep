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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        $logs = [];
        $logs[] = 'FetchNewsFeedJob started.';
        $logs[] = 'Base URI: ' . $this->baseUri;
        Log::info('FetchNewsFeedJob started.');

        $this->authenticate($logs);
        $admin = $this->getAuthor();

        $logs[] = "Admin ID: $admin->id";

        if (!$this->authToken) {
            Log::error('Auth token not available. Skipping news feed fetch.');
            $logs[] = 'Auth token not available. Skipping news feed fetch.';
            return $logs;
        }


        $lastIndexedDocument = null;
        try {
            $results = app('search')->search('news_feed', '', [
                'sort' => ['report_date_published:desc'],
                'limit' => 1,
            ]);
            $hits = $results['hits'] ?? null;
            $lastIndexedDocument = $hits ? $hits[0] : null;
        } catch (\Exception $e) {
            Log::warning('Index "news_feed" does not exist. Continuing without previous documents.');
            $logs[] = 'Index "news_feed" does not exist. Continuing without previous documents.';
        }

        $startDate = $lastIndexedDocument && isset($lastIndexedDocument['report_date_published'])
            ? Carbon::parse($lastIndexedDocument['report_date_published'])->format('Y-m-d')
            : now()->format('Y-m-d');

        $endDate = now()->format('Y-m-d');

        if ($lastIndexedDocument) {
            $logs[] = "Last indexed document: " . json_encode([
                'report_title' => $lastIndexedDocument['report_title'] ?? 'N/A',
                'report_date_published' => $lastIndexedDocument['report_date_published'] ?? 'N/A'
            ]);
        }

        $logs[] = "Start Date: $startDate";
        $logs[] = "End Date: $endDate";


        $response = Http::timeout(1800)->
            withHeaders($this->getAuthorizationHeader())
            ->get("{$this->baseUri}/viewer/reports/as-geojson", [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

        if ($response->successful()) {
            $features = $response->json()['data']['features'] ?? [];

            // Extract all properties for bulk indexing
            $reportsData = array_map(fn ($feature) => array_merge(
                $feature['properties'],
                ['post_type' => 'newsfeed'],
                ['author' => $admin->name],
                ['author_id' => $admin->id],
            ), $features);

            $sortableAttributes = [
                'report_date_published', 'report_title',
                'place_geocode_name', 'place_admin_level',];
            $filterableAttributes = ['place_geocode_name', 'source_name', 'entity_value'];


            $result = app('search')->indexData(
                indexName: 'news_feed',
                data: $reportsData,
                sortableAttributes: $sortableAttributes,
                filterableAttributes: $filterableAttributes,
                primaryKey: 'report_id',
            );
            Log::info($result . ' News feed successfully indexed.');
            $logs[] = "$result News feed successfully indexed.";
        } else {
            Log::error('Failed to fetch news feed.', ['response' => $response->body()]);
            $logs[] = 'Failed to fetch news feed. Response: ' . $response->body();
        }

        return $logs;
    }

    /**
     * Authenticate to retrieve the auth token.
     */
    protected function authenticate(array &$logs)
    {
        if (Cache::has('news_api_auth_token')) {
            $this->authToken = Cache::get('news_api_auth_token');
            $logs[] = 'Using cached auth token.';
            return;
        }

        $response = Http::asForm()->post("{$this->baseUri}/accounts/login", [
        'username' => env('NEWS_API_EMAIL'),
        'password' => env('NEWS_API_PASSWORD'),
        ]);

        $logs[] = 'Authenticating... with ' . env('NEWS_API_EMAIL');

        if ($response->successful()) {
            $data = $response->json()['data'] ?? [];
            $this->authToken = $data['token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 1000;

            Cache::put('news_api_auth_token', $this->authToken, now()->addSeconds($expiresIn));

            Log::info('Authenticated and cached auth token with dynamic expiration.', [
                'expires_in' => $expiresIn,
            ]);
            $logs[] = "Authenticated and cached auth token for $expiresIn seconds.";
        } else {
            Log::error('Authentication failed.', ['response' => $response->body()]);
            $logs[] = 'Authentication failed. Response: ' . $response->body();
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

    protected function getAuthor()
    {
        $user = DB::table('accounts')->where('email', env('NEWS_API_EMAIL'))->first();

        return $user ?: null;
    }
}
