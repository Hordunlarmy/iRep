<?php

namespace App\Services;

use MeiliSearch\Client;

class MeilisearchService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(env('MEILISEARCH_HOST'), env('MEILISEARCH_KEY'));
    }

    // Indexing data using raw SQL
    public function indexData(string $indexName, array $data): void
    {
        // Filter out null values from the data
        $filteredData = array_map(function ($item) {
            return array_filter($item, function ($value) {
                return !is_null($value);
            });
        }, $data);

        $index = $this->client->index($indexName);
        $index->addDocuments($filteredData);
    }

    // Search data in Meilisearch
    public function search(string $indexName, string $query, array $filters = []): array
    {
        $index = $this->client->index($indexName);
        return $index->search($query, ['filter' => $filters])->getHits();
    }

    public function clearAllIndexes(): void
    {
        $indexes = $this->client->getIndexes();

        if (is_array($indexes) || $indexes instanceof \Traversable) {
            foreach ($indexes as $index) {
                $this->client->index($index->uid)->delete();
            }
        } else {
            echo 'No indexes found or an unexpected response format.';
        }

        echo 'All indexes have been deleted successfully.';
    }

}
