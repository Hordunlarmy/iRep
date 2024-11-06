<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SearchEngineService;

class SearchEngineServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('search', function ($app) {
            return new SearchEngineService();
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
