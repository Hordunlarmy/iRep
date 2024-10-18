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

    public function getRepresentatives($criteria)
    {
        $page = $criteria['page'] ?? 1;
        $pageSize = $criteria['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;
        $params = [2];

        $query = '
		SELECT a.id, a.name, a.account_type, a.photo_url, a.state,
		a.local_government, r.position, r.party, r.constituency
		FROM accounts a
		JOIN representatives r ON r.account_id = a.id
		WHERE a.account_type = ?';

        list($query, $params) = $this->applyFilters($query, $params, $criteria);
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

    private function applyFilters($query, $params, array $criteria)
    {
        $search = $criteria['search'] ?? null;
        $stateFilter = $criteria['state'] ?? null;
        $positionFilter = $criteria['position'] ?? null;
        $localGovtFilter = $criteria['local_government'] ?? null;

        if ($search) {
            $query .= ' AND (a.name LIKE ? OR a.email LIKE ? OR a.phone_number LIKE ?)';
            $params = array_merge($params, array_fill(0, 3, '%' . $search . '%'));
        }

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

        list($query, $params) = $this->applyFilters($query, $params, $criteria);
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

}
