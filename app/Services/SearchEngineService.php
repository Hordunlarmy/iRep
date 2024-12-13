<?php

namespace App\Services;

use Meilisearch\Client;
use Illuminate\Support\Facades\Log;

class SearchEngineService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(env('MEILISEARCH_HOST'), env('MEILISEARCH_KEY'));
    }

    // Indexing data using raw SQL
    public function indexData(
        string $indexName,
        array $data,
        array $sortableAttributes = [],
        array $filterableAttributes = [],
        string|int|null $primaryKey = null,
    ): int {
        // Filter out null values from the data
        $filteredData = app('utils')->filterNullValues($data);

        $index = $this->client->index($indexName);

        if ($primaryKey !== null) {
            $index->update(['primaryKey' => $primaryKey]);
        }

        if (!empty($sortableAttributes)) {
            $index->updateSortableAttributes($sortableAttributes);
        }

        if (!empty($filterableAttributes)) {
            $index->updateFilterableAttributes($filterableAttributes);
        }

        $index->addDocuments($filteredData);

        return count($filteredData);
    }

    // Search data in Meilisearch
    public function search(string $indexName, string $query = '', array $options = []): array
    {
        $index = $this->client->index($indexName);
        $response = $index->search($query, $options)->getRaw();

        return $response;
    }

    // Clear all indexes
    public function clearAllIndexes(): void
    {
        $indexes = $this->client->getIndexes();

        if (is_array($indexes) || $indexes instanceof \Traversable) {
            foreach ($indexes as $index) {
                $this->client->index($index->uid)->delete();
            }
        } else {
            Log::info('No indexes found or an unexpected response format.');
            return;
        }

        Log::info('All indexes have been deleted successfully.');
    }

    // Delete a specific document from an index
    public function deleteData(string $indexName, string|int $documentId): void
    {
        $index = $this->client->index($indexName);
        $index->deleteDocument($documentId);

        Log::info("Document with ID {$documentId} has been deleted successfully.");
    }

    // Delete an entire index
    public function deleteIndex(string $indexName): void
    {
        $this->client->deleteIndex($indexName);

        Log::info("Index '{$indexName}' has been deleted successfully.");
    }
}
