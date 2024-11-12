<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchNewsFeedJob;

class FetchNewsFeedCommand extends Command
{
    protected $signature = 'news:fetch';
    protected $description = 'Fetch and index news feed data.';

    public function handle()
    {
        $fetchNewsFeedJob = new FetchNewsFeedJob();
        $logs = $fetchNewsFeedJob->handle();

        foreach ($logs as $logMessage) {
            $this->info($logMessage);
        }
    }
}
