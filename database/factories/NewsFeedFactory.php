<?php

namespace Database\Factories;

class NewsFeedFactory
{
    protected $baseUri = env('NEWS_FEED_BASE_URI');

    public function __construct($baseUri = null)
    {
        $this->baseUri = $baseUri ?? $this->baseUri;
    }


}
