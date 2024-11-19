<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomePageFactory extends PostFactory
{
    protected $db;

    public function __construct($db = null)
    {
        parent::__construct();
        $this->db = $db ?: DB::connection()->getPdo();
    }

    public function globalSearch(array $criteria = []): array
    {
        $query = $criteria['search'] ?? '';
        if (empty($query)) {
            return [];
        };

        $indexes = ['posts', 'accounts', 'news_feed'];
        $page = $criteria['page'] ?? 1;
        $pageSize = $criteria['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;
        $sortBy = $criteria['sort_by'] ?? 'created_at';
        $sortOrder = $criteria['sort_order'] ?? 'desc';
        $filters = $criteria['filters'] ?? [];

        $searchParams = [
            'filter' => $this->buildFilters($filters),
            'limit' => (int) $pageSize,
            'offset' => (int) $offset,
            # 'sort' => ["$sortBy:$sortOrder"],
            'attributesToRetrieve' => ['*'],
        ];

        $categorizedResults = [];

        foreach ($indexes as $indexName) {
            $results = app('search')->search($indexName, $query, $searchParams);
            $hits = $results['hits'] ?? [];
            $totalCount = $results['nbHits'] ?? 0;

            foreach ($hits as &$hit) {
                if ($indexName === 'accounts' && isset($hit['photo_url'])) {
                    $hit['photo_url'] = json_decode($hit['photo_url'], true);
                }
                if ($indexName === 'posts' && isset($hit['media'])) {
                    $hit['media'] = json_decode($hit['media'], true);
                }
            }

            $categorizedResults[$indexName] = [
                'data' => $hits,
                'meta' => [
                    'total' => (int) $totalCount,
                    'current_page' => (int) $page,
                    'last_page' => (int) ceil($totalCount / $pageSize),
                    'page_size' => (int) $pageSize,
                ],
            ];
        }

        return $categorizedResults;
    }

    public function getCommunityPosts(array $criteria = [])
    {
        try {
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

            $results = app('search')->search('posts', $query, $searchParams);

            $totalCount = $results['nbHits'] ?? 0;
            $lastPage = ceil($totalCount / $pageSize);

            return [
                'data' => $results['hits'] ?? [],
                'total' => $totalCount,
                'current_page' => $page,
                'last_page' => $lastPage,
                'page_size' => (int) $pageSize,
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching posts from meillisearch: ' . $e->getMessage());
            $this->getPosts($criteria);
        }
    }

    public function getRepresentatives(array $criteria = [])
    {
        try {
            $page = $criteria['page'] ?? 1;
            $pageSize = $criteria['page_size'] ?? 10;
            $offset = ($page - 1) * $pageSize;
            $query = $criteria['search'] ?? '';
            $sortBy = $criteria['sort_by'] ?? 'created_at';
            $sortOrder = $criteria['sort_order'] ?? 'desc';

            $filters = [
                'account_type' => 'representative',
                'state' => $criteria['state'] ?? null,
                'local_government' => $criteria['local_government'] ?? null,
                'position' => $criteria['position'] ?? null,
                'constituency' => $criteria['constituency'] ?? null,
                'party' => $criteria['party'] ?? null,
                'district' => $criteria['district'] ?? null,
            ];

            $searchParams = [
                'filter' => $this->buildFilters($filters),
                'limit' => (int) $pageSize,
                'offset' => (int) $offset,
                'sort' => ["$sortBy:$sortOrder"],
                'attributesToRetrieve' => ['*'],
            ];

            $results = app('search')->search('accounts', $query, $searchParams);

            $totalCount = $results['nbHits'] ?? 0;
            $lastPage = ceil($totalCount / $pageSize);

            return [
                'data' => $results['hits'] ?? [],
                'total' => $totalCount,
                'current_page' => $page,
                'last_page' => $lastPage,
                'page_size' => (int) $pageSize,

            ];
        } catch (\Exception $e) {
            Log::error('Error fetching representatives from meillisearch: ' . $e->getMessage());
            $this->getRepresentativesFromDatabase($criteria);
        }
    }



    public function getRepresentativesFromDatabase($criteria)
    {
        $page = $criteria['page'] ?? 1;
        $pageSize = $criteria['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;
        $params = [2];

        $query = '
		SELECT a.id, a.name, a.account_type, a.photo_url, s.name AS state,
		l.name As local_government, p.title AS position , pa.name AS party,
	   	c.name AS constituency
		FROM accounts a
		JOIN representatives r ON r.account_id = a.id
		LEFT JOIN states s ON a.state_id = s.id
		LEFT JOIN local_governments l ON a.local_government_id = l.id
		LEFT JOIN positions p ON r.position_id = p.id
		LEFT JOIN parties pa ON r.party_id = pa.id
		LEFT JOIN constituencies c ON r.constituency_id = c.id
		WHERE a.account_type = ?';

        list($query, $params) = $this->applyFilters($query, $params, $criteria, 'representative');
        $query = $this->applySorting($query, $criteria);

        $query .= " LIMIT ? OFFSET ?";
        $params[] = (int) $pageSize;
        $params[] = (int) $offset;

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $representatives = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Count total records for pagination
            $countQuery = '
			SELECT COUNT(*) AS total
			FROM accounts a
			JOIN representatives r ON r.account_id = a.id
			WHERE a.account_type = ?';
            $countParams = [2];

            list($countQuery, $countParams) = $this->applyFilters($countQuery, $countParams, $criteria);

            $totalCountStmt = $this->db->prepare($countQuery);
            $totalCountStmt->execute($countParams);
            $totalCount = $totalCountStmt->fetchColumn();

            return [
                'data' => $representatives,
                'total' => $totalCount,
                'current_page' => $page,
                'last_page' => ceil($totalCount / $pageSize),
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching representatives: ' . $e->getMessage());
            return [];
        }
    }

    protected function buildFilters(array $filters): string
    {
        $meiliFilters = [];
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $meiliFilters[] = "$field IN [" . implode(',', array_map(fn ($v) => "\"$v\"", $value)) . "]";
            } elseif (!is_null($value)) {
                $meiliFilters[] = "$field = \"$value\"";
            }
        }

        return implode(' AND ', $meiliFilters);
    }


    private function applyFilters($query, $params, array $criteria, $context = 'representative')
    {
        $search = $criteria['search'] ?? null;
        $stateFilter = $criteria['state'] ?? null;
        $positionFilter = $criteria['position'] ?? null;
        $localGovtFilter = $criteria['local_government'] ?? null;
        $titleFilter = $criteria['title'] ?? null;
        $contentFilter = $criteria['content'] ?? null;

        if ($search) {
            if ($context === 'representative') {
                $query .= ' AND (a.name LIKE ? OR a.email LIKE ? OR a.phone_number LIKE ?)';
                $params = array_merge($params, array_fill(0, 3, '%' . $search . '%'));
            } elseif ($context === 'post') {
                $query .= ' AND (p.title LIKE ? OR p.content LIKE ?)';
                $params = array_merge($params, array_fill(0, 2, '%' . $search . '%'));
            }
        }

        if ($context === 'representative') {
            if ($stateFilter) {
                $query .= ' AND a.state = ?';
                $params[] = $stateFilter;
            }

            if ($positionFilter) {
                $query .= ' AND r.position = ?';
                $params[] = $positionFilter;
            }

            if ($localGovtFilter) {
                $query .= ' AND a.local_government = ?';
                $params[] = $localGovtFilter;
            }
        } elseif ($context === 'post') {
            if ($titleFilter) {
                $query .= ' AND p.title LIKE ?';
                $params[] = '%' . $titleFilter . '%';
            }

            if ($contentFilter) {
                $query .= ' AND p.content LIKE ?';
                $params[] = '%' . $contentFilter . '%';
            }
        }

        return [$query, $params];
    }

    private function applySorting($query, array $criteria)
    {
        $sortBy = $criteria['sort_by'] ?? 'created_at';
        $sortOrder = $criteria['sort_order'] ?? 'desc';

        $allowedSortColumns = ['created_at', 'name', 'constituency', 'state'];
        $allowedSortOrders = ['asc', 'desc'];

        if (in_array($sortBy, $allowedSortColumns) && in_array($sortOrder, $allowedSortOrders)) {
            $query .= " ORDER BY {$sortBy} {$sortOrder}";
        }

        return $query;
    }

}
