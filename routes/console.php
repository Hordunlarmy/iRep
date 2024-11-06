<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\IndexExistingData;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\FetchNewsFeedJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('meilisearch:index-existing', function () {
    $this->call(IndexExistingData::class);
})->purpose('Index existing data into Meilisearch');

Schedule::job(new FetchNewsFeedJob())->everyMinute();
