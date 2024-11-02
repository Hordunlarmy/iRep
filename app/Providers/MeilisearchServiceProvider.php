<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MeilisearchService;

class MeilisearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('meilisearch', function ($app) {
            return new MeilisearchService();
        });

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
