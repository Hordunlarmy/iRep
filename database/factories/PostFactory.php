<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Petition;
use App\Models\EyeWitnessReport;

class PostFactory extends CommentFactory
{
    protected $db;

    public function __construct($db = null)
    {
        parent::__construct($db);
    }

    public function createPost($data)
    {
        try {
            $this->db->beginTransaction();

            $post = new Post($this->db, $data);
            $postId = $post->insertPost();

            if ($data['post_type'] === 'petition') {
                (new Petition($postId, $data))->insert($this->db);
            } elseif ($data['post_type'] === 'eyewitness') {
                (new EyeWitnessReport($postId, $data))->insert($this->db);
            }

            $this->db->commit();

            return $postId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getPosts(array $criteria = [])
    {
        $page = $criteria['page'] ?? 1;
        $pageSize = $criteria['pageSize'] ?? 10;
        $offset = ($page - 1) * $pageSize;
        $params = [];

        $query = "
		SELECT
			p.id,
			p.title,
			p.context,
			p.media,
			p.post_type,
			p.creator_id,
			p.created_at,
			CASE
				WHEN p.post_type = 'petition' THEN JSON_OBJECT(
					'target_representative_id', pe.target_representative_id,
					'signatures', pe.signatures,
					'status', pe.status
				)
				WHEN p.post_type = 'eyewitness' THEN JSON_OBJECT(
					'approvals', ew.approvals,
					'category', ew.category
				)
			END AS post_data
		FROM posts p
		LEFT JOIN petitions pe ON p.id = pe.post_id
		LEFT JOIN eye_witness_reports ew ON p.id = ew.post_id
		WHERE 1=1";

        list($query, $params) = $this->applyFilters($query, $params, $criteria);
        $query = $this->applySorting($query, $criteria);

        $query .= " LIMIT ? OFFSET ?";
        $params[] = $pageSize;
        $params[] = $offset;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(\PDO::FETCH_CLASS);

        $countQuery = "SELECT COUNT(*) AS total FROM posts p
                   LEFT JOIN petitions pe ON p.id = pe.post_id
                   LEFT JOIN eye_witness_reports ew ON p.id = ew.post_id
                   WHERE 1=1";

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
    }

    private function applyFilters($query, $params, array $criteria)
    {
        $search = $criteria['search'] ?? null;
        $filter = $criteria['filter'] ?? null;

        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $query .= " AND (p.title LIKE ? OR p.context LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filter)) {
            $query .= " AND p.post_type = ?";
            $params[] = $filter;
        }

        return [$query, $params];
    }

    private function applySorting($query, array $criteria)
    {
        $sortBy = $criteria['sort_by'] ?? 'created_at';
        $sortOrder = $criteria['sort_order'] ?? 'desc';

        $allowedSortColumns = ['created_at', 'title'];
        $allowedSortOrders = ['asc', 'desc'];

        if (in_array($sortBy, $allowedSortColumns) && in_array($sortOrder, $allowedSortOrders)) {
            $query .= " ORDER BY {$sortBy} {$sortOrder}";
        }

        return $query;
    }

    public function getPost($postId)
    {
        $query = "
		SELECT
		p.id,
		p.title,
		p.context,
		p.media,
		p.post_type,
		p.creator_id,
		p.created_at,
		CASE
		WHEN p.post_type = 'petition' THEN JSON_OBJECT(
		'target_representative_id', pe.target_representative_id,
		'signatures', pe.signatures,
		'status', pe.status
		)
		WHEN p.post_type = 'eyewitness' THEN JSON_OBJECT(
		'approvals', ew.approvals,
		'category', ew.category
		)
		END AS post_data
		FROM posts p
		LEFT JOIN petitions pe ON p.id = pe.post_id
		LEFT JOIN eye_witness_reports ew ON p.id = ew.post_id
		WHERE p.id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$postId]);

        return $stmt->fetchObject();
    }

    public function hasUserSigned($postId, $accountId)
    {
        $query = "
		SELECT COUNT(*)
		FROM petition_signatures
		WHERE post_id = ? AND account_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$postId, $accountId]);
        return $stmt->fetchColumn() > 0;
    }

    public function hasUserApproved($postId, $accountId)
    {
        $query = "
		SELECT COUNT(*)
		FROM eye_witness_reports_approvals
		WHERE post_id = ? AND account_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$postId, $accountId]);
        return $stmt->fetchColumn() > 0;
    }

    public function insertApproval($postId, $accountId, $comment = null)
    {
        try {
            $this->db->beginTransaction();

            $query = "
			INSERT INTO eye_witness_reports_approvals (post_id, account_id)
			VALUES (?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$postId, $accountId]);

            $incrementQuery = "
			UPDATE eye_witness_reports
			SET approvals = approvals + 1
			WHERE post_id = ?";

            $incrementStmt = $this->db->prepare($incrementQuery);
            $incrementStmt->execute([$postId]);

            if ($comment) {
                $data = [
                    'postId' => $postId,
                    'accountId' => $accountId,
                    'comment' => $comment,
                ];
                $this->insertComment($data);
            }

            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }


    public function insertSignature($postId, $accountId, $comment = null)
    {
        try {
            $this->db->beginTransaction();

            $query = "
			INSERT INTO petition_signatures (post_id, account_id)
			VALUES (?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$postId, $accountId]);

            $incrementQuery = "
			UPDATE petitions
			SET signatures = signatures + 1
			WHERE post_id = ?";

            $incrementStmt = $this->db->prepare($incrementQuery);
            $incrementStmt->execute([$postId]);

            if ($comment) {
                $data = [
                    'postId' => $postId,
                    'accountId' => $accountId,
                    'comment' => $comment,
                ];
                $this->insertComment($data);
            }

            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

}
