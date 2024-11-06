<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Register broadcasting routes with JWT authentication
        Broadcast::routes([
        'middleware' => ['auth:api'],
        'prefix' => 'api',
        ]);

        // Load channel definitions
        require base_path('routes/channels.php');
    }
}
