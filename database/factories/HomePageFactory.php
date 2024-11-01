<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class homePageFactory
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::connection()->getPdo();
    }

    public function globalSearch($criteria)
    {
        $page = $criteria['page'] ?? 1;
        $pageSize = $criteria['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;
        $searchTerm = $criteria['search'] ?? null;

        $params = array_fill(0, 6, '%' . $searchTerm . '%');

        $query = '
		SELECT p.id AS post_id, p.title, p.content, p.media_url, p.created_at,
		a.name AS creator_name, a.account_type, a.photo_url AS creator_photo,
		r.position, r.party, "post" AS type
		FROM posts p
		JOIN accounts a ON p.creator_id = a.id
		LEFT JOIN representatives r ON a.id = r.account_id
		WHERE (p.title LIKE ? OR p.content LIKE ? OR a.name LIKE ? OR a.email LIKE ? OR r.position LIKE ? OR r.party LIKE ?)
		LIMIT ? OFFSET ?';

        $params[] = (int) $pageSize;
        $params[] = (int) $offset;

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $countQuery = '
			SELECT COUNT(*) AS total
			FROM posts p
			JOIN accounts a ON p.creator_id = a.id
			LEFT JOIN representatives r ON a.id = r.account_id
			WHERE (p.title LIKE ? OR p.content LIKE ? OR a.name LIKE ? OR a.email LIKE ? OR r.position LIKE ? OR r.party LIKE ?)';

            $totalCountStmt = $this->db->prepare($countQuery);
            $totalCountStmt->execute(array_slice($params, 0, 6));
            $totalCount = $totalCountStmt->fetchColumn();

            return [
                'data' => $results,
                'total' => $totalCount,
                'current_page' => $page,
                'last_page' => ceil($totalCount / $pageSize),
            ];
        } catch (\Exception $e) {
            Log::error('Error executing global search: ' . $e->getMessage());
            return [];
        }
    }


    public function getCommunityPosts($criteria)
    {
        $page = $criteria['page'] ?? 1;
        $pageSize = $criteria['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;
        $params = [];

        $query = '
		SELECT p.id, p.title, p.content, p.media_url, p.created_at, a.name, a.photo_url
		FROM posts p
		JOIN accounts a ON p.creator_id = a.id
		WHERE p.target_representative_id IS NULL';

        list($query, $params) = $this->applyFilters($query, $params, $criteria, 'post');
        $query = $this->applySorting($query, $criteria);

        $query .= " LIMIT ? OFFSET ?";
        $params[] = (int) $pageSize;
        $params[] = (int) $offset;

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Count total records for pagination
            $countQuery = '
			SELECT COUNT(*) AS total
			FROM posts p
			WHERE p.target_representative_id IS NULL';
            $countParams = [];

            list($countQuery, $countParams) = $this->applyFilters($countQuery, $countParams, $criteria);

            $totalCountStmt = $this->db->prepare($countQuery);
            $totalCountStmt->execute($countParams);
            $totalCount = $totalCountStmt->fetchColumn();

            return [
                'data' => $posts,
                'total' => $totalCount,
                'current_page' => $page,
                'last_page' => ceil($totalCount / $pageSize),
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching community posts: ' . $e->getMessage());
            return [];
        }
    }


    public function getRepresentatives($criteria)
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
