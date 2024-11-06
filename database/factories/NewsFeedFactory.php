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
        $sortBy = $criteria['sort_by'] ?? 'created_at';
        $sortOrder = $criteria['sort_order'] ?? 'desc';

        $filters = [
            'status' => $criteria['status'] ?? null,
            'category' => $criteria['category'] ?? null,
            'post_type' => $criteria['post_type'] ?? null,
        ];

        $searchParams = [
            'filter' => $this->buildFilters($filters),
            'limit' => (int) $pageSize,
            'offset' => (int) $offset,
            'sort' => ["$sortBy:$sortOrder"],
            'attributesToRetrieve' => ['*'],
        ];

        $results = app('meilisearch')->search('news-feed', $query, $searchParams);

        $totalCount = $results['nbHits'] ?? 0;
        $lastPage = ceil($totalCount / $pageSize);

        return [
            'data' => $results['hits'] ?? [],
            'total' => $totalCount,
            'current_page' => $page,
            'last_page' => $lastPage,
        ];
    }

}
