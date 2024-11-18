<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PushNotificationService;

class PushNotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('pushNotify', function ($app) {
            return new PushNotificationService();
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
