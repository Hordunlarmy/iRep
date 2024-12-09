<?php

namespace App\Admin\Factories;

use Illuminate\Support\Facades\DB;
use App\Admin\Models\Admin;
use Illuminate\Support\Facades\Log;

class ContentModerationFactory
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::connection()->getPdo();
    }

    public function getPostStats($type)
    {
        $query = "
			SELECT
				COUNT(*) AS total_{$type},
				COUNT(CASE WHEN r.entity_type = 'post' THEN 1 END) AS reported_{$type},
				COUNT(CASE WHEN p.status = 'inactive' THEN 1 END) AS ignored_{$type}
			FROM posts p
			LEFT JOIN reports r ON p.id = r.entity_id AND r.entity_type = 'post'
			WHERE p.post_type = :type
		";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':type', $type, \PDO::PARAM_STR);
        $stmt->execute();

        // Fetch and return the result
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getContents(array $criteria = [])
    {
        $page = $criteria['page'] ?? 1;
        $pageSize = $criteria['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;
        $params = [];

        // Extract allowed states from the 'states' table
        $allowedStatesStmt = $this->db->prepare("SELECT name FROM states");
        $allowedStatesStmt->execute();
        $allowedStates = $allowedStatesStmt->fetchAll(\PDO::FETCH_COLUMN);

        // Initialize state filter if provided
        $stateFilter = '';
        if (!empty($criteria['states'])) {
            $states = array_intersect($criteria['states'], $allowedStates);
            if (!empty($states)) {
                $stateFilter = "AND s.state IN (" . implode(',', array_fill(0, count($states), '?')) . ")";
                $params = array_merge($params, $states);
            }
        }

        // Initialize status filter if provided
        $statusFilter = '';
        if (isset($criteria['status']) && in_array($criteria['status'], ['active', 'inactive'])) {
            $statusFilter = "AND p.status = ?";
            $params[] = $criteria['status'];
        }

        // Initialize report filter if provided
        $reportFilter = '';
        if (isset($criteria['reported']) && $criteria['reported'] === true) {
            $reportFilter = "AND r.entity_id IS NOT NULL";
        }

        $query = "
        SELECT
            p.id,
            p.title,
            p.context,
            p.media,
            p.post_type,
            a.name AS author,
            a.kyced AS author_kyced,
            a.account_type AS author_account_type,
            a.id AS author_id,
            a.photo_url AS author_photo,
			p.created_at,
			CASE
				 WHEN a.status = 'inactive' THEN r.reason
			ELSE NULL
			END AS report_reason,
            CASE
                WHEN p.post_type = 'petition' THEN JSON_OBJECT(
                    'target_representative', rep.name,
                    'signatures', pe.signatures,
                    'target_signatures', pe.target_signatures,
                    'status', pe.status
                )
                WHEN p.post_type = 'eyewitness' THEN JSON_OBJECT(
                    'approvals', ew.approvals,
                    'category', ew.category
				)
				ELSE NULL
            END AS post_data
        FROM posts p
        LEFT JOIN petitions pe ON p.id = pe.post_id
        LEFT JOIN eye_witness_reports ew ON p.id = ew.post_id
        LEFT JOIN accounts a ON p.creator_id = a.id
		LEFT JOIN accounts rep ON pe.target_representative_id = rep.id
		LEFT JOIN states s ON a.state_id = s.id
        LEFT JOIN reports r ON r.entity_id = p.id AND r.entity_type = 'post'
        WHERE 1=1
        $stateFilter
        $statusFilter
        $reportFilter
        LIMIT :offset, :page_size
    ";

        // Prepare and execute the query
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindParam(':page_size', $pageSize, \PDO::PARAM_INT);

        // Bind state parameters if any
        foreach ($params as $key => $param) {
            $stmt->bindValue($key + 1, $param, \PDO::PARAM_STR);
        }

        $stmt->execute();
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch total count using the separate method
        $totalCount = $this->getPostCount($stateFilter, $params, $statusFilter, $reportFilter);

        return [
            'data' => $posts,
            'meta' => [
                'current_page' => (int) $page,
                'page_size' => (int) $pageSize,
                'total_records' => $totalCount,
                'total_pages' => ceil($totalCount / $pageSize),
            ],
        ];

    }

    public function getPostCount($stateFilter = '', $params = [], $statusFilter = '', $reportFilter = '')
    {
        $countQuery = "
			SELECT COUNT(*) FROM posts p
			LEFT JOIN petitions pe ON p.id = pe.post_id
			LEFT JOIN eye_witness_reports ew ON p.id = ew.post_id
			LEFT JOIN accounts a ON p.creator_id = a.id
			LEFT JOIN accounts rep ON pe.target_representative_id = rep.id
			LEFT JOIN states s ON a.state_id = s.id
			LEFT JOIN reports r ON r.entity_id = p.id AND r.entity_type = 'post'
			WHERE 1=1
			$stateFilter
			$statusFilter
			$reportFilter
		";

        // Prepare and execute the count query
        $stmt = $this->db->prepare($countQuery);

        // Bind state parameters for the count query
        foreach ($params as $key => $param) {
            $stmt->bindValue($key + 1, $param, \PDO::PARAM_STR);
        }

        // Bind status filter parameters if any
        if ($statusFilter) {
            $stmt->bindValue(count($params) + 1, $params[count($params) - 1], \PDO::PARAM_STR);
        }

        // Bind report filter parameters if any
        if ($reportFilter) {
            $stmt->bindValue(count($params) + 1, null, \PDO::PARAM_NULL);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
