<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MediaHandler;

class MediaHandlerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton('uploadMediaService', function ($app) {
            return new MediaHandler();
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
