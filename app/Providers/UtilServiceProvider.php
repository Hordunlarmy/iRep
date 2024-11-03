<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Utils;

class UtilServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('utils', function ($app) {
            return new Utils();
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
