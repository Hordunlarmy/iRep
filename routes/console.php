<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\FetchNewsFeedCommand;
use App\Console\Commands\IndexExistingData;
use App\Console\Commands\CreateSuperAdmin;
use App\Jobs\FetchNewsFeedJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('search:index-existing', function () {
    $this->call(IndexExistingData::class);
})->purpose('Index existing data into SearchEngine');

Artisan::command('news:fetch', function () {
    $this->call(FetchNewsFeedCommand::class);
})->purpose('Fetch and index news feed data.');

Artisan::command('create:superadmin {username} {password}', function ($username, $password) {
    $this->call(CreateSuperAdmin::class, [
        'username' => $username,
        'password' => $password
    ]);
})->purpose('Create a super admin with given username and password');

Schedule::job(new FetchNewsFeedJob())->everyMinute();
