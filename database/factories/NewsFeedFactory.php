<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;

class NewsFeedFactory extends HomePageFactory
{
    protected $db;

    public function __construct($db = null)
    {
        parent::__construct();
        $this->db = $db ?: DB::connection()->getPdo();
    }

    public function getNewsFeed($criteria)
    {
        $page = $criteria['page'] ?? 1;
        $pageSize = $criteria['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;
        $query = $criteria['search'] ?? '';
        $sortBy = $criteria['sort_by'] ?? 'report_date_published';
        $sortOrder = $criteria['sort_order'] ?? 'desc';

        $filters = [
            'place_geocode_name' => $criteria['place_geocode_name'] ?? null,
            'source_name' => $criteria['source_name'] ?? null,
            'entity_value' => $criteria['entity_value'] ?? null,
        ];

        $searchParams = [
            'filter' => $this->buildFilters($filters),
            'limit' => (int) $pageSize,
            'offset' => (int) $offset,
            'sort' => ["$sortBy:$sortOrder"],
            'attributesToRetrieve' => ['*'],
        ];

        $results = app('search')->search('news_feed', $query, $searchParams);

        $totalCount = $results['nbHits'] ?? 0;
        $lastPage = ceil($totalCount / $pageSize);

        return [
            'data' => $results['hits'] ?? [],
            'total' => $totalCount,
            'current_page' => (int) $page,
            'last_page' => $lastPage,
        ];
    }

}
