<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Email;
use App\Services\EmailTemplates;

class EmailServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('emailService', function ($app) {

            EmailTemplates::init();
            $templates = new EmailTemplates();

            return new Email($templates);
        });
    }

    public function boot()
    {
    }
}
