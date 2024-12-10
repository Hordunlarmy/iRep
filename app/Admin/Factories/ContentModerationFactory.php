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
				COUNT(DISTINCT p.id) AS total_{$type},
				COUNT(DISTINCT CASE WHEN r.entity_type = 'post' THEN p.id END) AS reported_{$type},
				COUNT(DISTINCT CASE WHEN p.status = 'inactive' THEN p.id END) AS ignored_{$type}
			FROM posts p
			LEFT JOIN reports r ON p.id = r.entity_id AND r.entity_type = 'post'
			WHERE p.post_type = :type
		";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':type', $type, \PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getContents(array $criteria = [])
    {
        $page = $criteria['page'] ?? 1;
        $pageSize = $criteria['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;

        $params = [
            ':offset' => $offset,
            ':page_size' => $pageSize,
        ];

        $allowedStatesStmt = $this->db->prepare("SELECT name FROM states");
        $allowedStatesStmt->execute();
        $allowedStates = $allowedStatesStmt->fetchAll(\PDO::FETCH_COLUMN);

        $stateFilter = '';
        $stateParams = [];
        if (!empty($criteria['states'])) {
            $states = array_intersect($criteria['states'], $allowedStates);
            if (!empty($states)) {
                $statePlaceholders = [];
                foreach ($states as $index => $state) {
                    $placeholder = ":state_$index";
                    $statePlaceholders[] = $placeholder;
                    $stateParams[$placeholder] = $state;
                }
                $stateFilter = "AND s.name IN (" . implode(', ', $statePlaceholders) . ")";
            }
        }

        $statusFilter = '';
        if (isset($criteria['status']) && in_array($criteria['status'], ['active', 'inactive'])) {
            $statusFilter = "AND p.status = :status";
            $params[':status'] = $criteria['status'];
        }

        $reportFilter = '';
        if (isset($criteria['reported']) && $criteria['reported'] === 'true') {
            $reportFilter = "AND r.entity_id IS NOT NULL";
        }

        $postTypeFilter = '';
        $postTypeJoin = '';
        $postTypeFields = '';

        if (!empty($criteria['post_type'])) {
            $postTypeFilter = "AND p.post_type = :post_type";
            $params[':post_type'] = $criteria['post_type'];

            if ($criteria['post_type'] === 'petition') {
                $postTypeJoin = "
                LEFT JOIN petitions pe ON p.id = pe.post_id
                LEFT JOIN accounts rep ON pe.target_representative_id = rep.id
                LEFT JOIN states s ON rep.state_id = s.id";
                $postTypeFields = "
                JSON_OBJECT(
                    'target_representative', rep.name,
                    'signatures', pe.signatures,
                    'target_signatures', pe.target_signatures,
                    'status', pe.status
                ) AS post_data";
            } elseif ($criteria['post_type'] === 'eyewitness') {
                $postTypeJoin = "
                LEFT JOIN eye_witness_reports ew ON p.id = ew.post_id";
                $postTypeFields = "
                JSON_OBJECT(
                    'approvals', ew.approvals,
                    'category', ew.category
                ) AS post_data";
            }
        }

        if (!$postTypeFields) {
            $postTypeFields = "NULL AS post_data";
        }

        $query = "
			SELECT DISTINCT
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
				r.reason AS reported,
				p.status,
				$postTypeFields
			FROM posts p
			LEFT JOIN accounts a ON p.creator_id = a.id
			LEFT JOIN reports r ON r.entity_id = p.id AND r.entity_type = 'post'
			$postTypeJoin
			WHERE 1=1
			$stateFilter
			$statusFilter
			$reportFilter
			$postTypeFilter
			LIMIT :offset, :page_size
		";

        // Merge all params
        $params = array_merge($params, $stateParams);

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }

        $stmt->execute();
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalCount = $this->getPostCount(
            $stateFilter,
            $statusFilter,
            $reportFilter,
            $postTypeJoin,
            $criteria
        );

        return [
            'data' => $posts,
            'meta' => [
                'current_page' => (int) $page,
                'page_size' => (int) $pageSize,
                'total_count' => $totalCount,
                'total_pages' => ceil($totalCount / $pageSize),
            ],
        ];
    }

    public function getPostCount(
        $stateFilter = '',
        $statusFilter = '',
        $reportFilter = '',
        $postTypeJoin = '',
        array $criteria = []
    ) {
        $params = [];

        $stateParams = [];
        if (!empty($criteria['states'])) {
            foreach ($criteria['states'] as $index => $state) {
                $stateParams[":state_$index"] = $state;
            }
        }

        if (isset($criteria['status']) && in_array($criteria['status'], ['active', 'inactive'])) {
            $params[':status'] = $criteria['status'];
        }

        if (!empty($criteria['post_type'])) {
            $params[':post_type'] = $criteria['post_type'];
        }

        $countQuery = "
			SELECT COUNT(DISTINCT p.id)
			FROM posts p
			LEFT JOIN accounts a ON p.creator_id = a.id
			LEFT JOIN reports r ON r.entity_id = p.id
				AND r.entity_type = 'post'
			$postTypeJoin
			WHERE 1=1
			$stateFilter
			$statusFilter
			$reportFilter
			";

        if (!empty($criteria['post_type'])) {
            $countQuery .= " AND p.post_type = :post_type";
        }

        $params = array_merge($params, $stateParams);

        $stmt = $this->db->prepare($countQuery);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function deletePost($postId, $postType)
    {
        $query = "DELETE FROM posts WHERE id = :id AND post_type = :post_type";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $postId, \PDO::PARAM_INT);
        $stmt->bindParam(':post_type', $postType, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function ignorePost($postId, $postType)
    {
        $query = "UPDATE posts SET status = 'inactive' WHERE id = :id AND post_type = :post_type";
        \Illuminate\Support\Facades\Log::info($query);
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $postId, \PDO::PARAM_INT);
        $stmt->bindParam(':post_type', $postType, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
