<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Petition;
use App\Models\EyeWitnessReport;
use Illuminate\Support\Facades\Log;

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

    public function indexPost($postId)
    {
        try {
            $fetchedPost = $this->getPost($postId);
            $postData = json_decode($fetchedPost->post_data, true);

            $dataToIndex = [
                'id' => $fetchedPost->id,
                'title' => $fetchedPost->title,
                'context' => $fetchedPost->context,
                'media' => $fetchedPost->media,
                'post_type' => $fetchedPost->post_type,
                'author_photo_url' => $fetchedPost->author_photo,
                'author_account_type' => $fetchedPost->author_account_type,
                'author_kyced' => $fetchedPost->author_kyced,
                'author' => $fetchedPost->author,
                'author_id' => $fetchedPost->author_id,
                'target_representatives' => $postData['target_representatives'] ?? null,
                'status' => $postData['status'] ?? null,
                'reported' => $fetchedPost->reported ?? null,
                'signatures' => $postData['signatures'] ?? null,
                'target_signatures' => $postData['target_signatures'] ?? null,
                'category' => $postData['category'] ?? null,
                'created_at' => $fetchedPost->created_at,

            ];

            $sortableAttributes = ['created_at', 'title', 'post_type'];
            $filterableAttributes = ['status', 'category', 'post_type', 'author'];

            $total = app('search')->indexData(
                'posts',
                [$dataToIndex],
                $sortableAttributes,
                $filterableAttributes,
                'id'
            );
            Log::info($total . ' Post Indexed');

        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function getPost($postId)
    {
        $query = "
        SELECT DISTINCT
            p.id,
            p.title,
            p.context,
            p.media,
            p.post_type,
            a.name AS author,
            a.photo_url AS author_photo,
            a.kyced AS author_kyced,
            a.account_type AS author_account_type,
            a.id AS author_id,
            r.reason AS reported,
            p.status AS post_status,
            p.created_at,
            CASE
                WHEN p.post_type = 'petition' THEN JSON_OBJECT(
                    'target_signatures', pe.target_signatures,
                    'signatures', pe.signatures,
                    'petition_status', pe.status,
                    'target_representatives', IFNULL(
                        JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'id', rep.id,
                                'name', rep.name,
                                'photo_url', rep.photo_url
                            )
                        ),
                        JSON_ARRAY()
                    )
                )
                WHEN p.post_type = 'eyewitness' THEN JSON_OBJECT(
                    'approvals', ew.approvals,
                    'category', ew.category
                )
            END AS post_data
        FROM posts p
        LEFT JOIN petitions pe ON p.id = pe.post_id
        LEFT JOIN eye_witness_reports ew ON p.id = ew.post_id
        LEFT JOIN accounts a ON p.creator_id = a.id
        LEFT JOIN petition_representatives pr ON pe.id = pr.petition_id
        LEFT JOIN accounts rep ON pr.representative_id = rep.id
        LEFT JOIN reports r ON r.entity_id = p.id AND r.entity_type = 'post'
        WHERE p.id = ?
        GROUP BY p.id, p.title, p.context, p.media, p.post_type,
            p.status, p.created_at, a.name, a.photo_url, a.kyced,
            a.id, a.account_type, pe.target_signatures, pe.signatures,
			a.account_type, pe.id, ew.id, r.reason, pe.status, ew.approvals,
			ew.category";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$postId]);

        return $stmt->fetchObject();
    }


    public function getPosts(array $criteria = [])
    {
        $page = $criteria['page'] ?? 1;
        $pageSize = $criteria['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;
        $params = [];

        $query = "
		SELECT
			p.id,
			p.title,
			p.context,
			p.post_type,
			p.media,
			a.name AS author,
			a.id AS author_id,
			a.photo_url AS author_photo,
			a.kyced AS author_kyced,
			a.account_type AS author_account_type,
			pe.status AS petition_status,
			p.status AS post_status,
			pe.signatures,
			pe.target_signatures,
			IFNULL((
				SELECT JSON_ARRAYAGG(
					JSON_OBJECT('id', rep.id, 'name', rep.name, 'photo_url', rep.photo_url)
				)
				FROM petition_representatives pr
				LEFT JOIN accounts rep ON pr.representative_id = rep.id
				WHERE pr.petition_id = pe.id
			), JSON_ARRAY()) AS target_representatives,
			ewr.category,
			p.created_at,
			r.reason AS reported
		FROM posts p
		LEFT JOIN petitions pe ON p.id = pe.post_id
		LEFT JOIN eye_witness_reports ewr ON p.id = ewr.post_id
		LEFT JOIN accounts a ON p.creator_id = a.id
		LEFT JOIN reports r ON r.entity_id = p.id AND r.entity_type = 'post'
		WHERE 1=1
		";

        list($query, $params) = $this->applyFilters($query, $params, $criteria);
        $query = $this->applySorting($query, $criteria);

        // Pagination logic
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $pageSize;
        $params[] = $offset;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(\PDO::FETCH_CLASS);

        // Total count query (distinct count of posts)
        $countQuery = "
		SELECT COUNT(DISTINCT p.id) AS total
		FROM posts p
		LEFT JOIN petitions pe ON p.id = pe.post_id
		LEFT JOIN eye_witness_reports ew ON p.id = ew.post_id
		WHERE 1=1
		";

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
        $creatorId = $criteria['creator_id'] ?? null;

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

        if (!empty($creatorId)) {
            $query .= " AND p.creator_id = ?";
            $params[] = $creatorId;
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

    public function getBookmarkedPosts($accountId)
    {
        $query = "
        SELECT DISTINCT
            p.id,
            p.title,
            p.context,
            p.post_type,
            p.media,
            a.name AS author,
            a.id AS author_id,
            a.photo_url AS author_photo_url,
            a.kyced AS author_kyced,
            a.account_type AS author_account_type,
            pe.status,
            pe.signatures,
            pe.target_signatures,
            IFNULL(
                JSON_ARRAYAGG(
                    JSON_OBJECT('id', rep.id, 'name', rep.name, 'photo_url', rep.photo_url)
                ),
                JSON_ARRAY()
            ) AS target_representatives,
            ew.category,
            p.created_at,
            r.reason AS reported
        FROM bookmarks
        JOIN posts p ON bookmarks.entity_id = p.id AND bookmarks.entity_type = 'post'
        LEFT JOIN petitions pe ON p.id = pe.post_id
        LEFT JOIN eye_witness_reports ew ON p.id = ew.post_id
        LEFT JOIN accounts a ON p.creator_id = a.id
        LEFT JOIN petition_representatives pr ON pe.id = pr.petition_id
        LEFT JOIN accounts rep ON pr.representative_id = rep.id
        LEFT JOIN reports r ON r.entity_id = p.id AND r.entity_type = 'post'
        WHERE bookmarks.account_id = ?
        GROUP BY p.id, p.title, p.context, p.post_type, p.media,
            a.name, a.id, a.photo_url, a.kyced,
            a.account_type, pe.status, pe.signatures,
            pe.target_signatures, ew.category,
            p.created_at, r.reason";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$accountId]);
        $posts = $stmt->fetchAll(\PDO::FETCH_CLASS);

        return $posts;
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

            $this->indexPost($postId);
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

            $status = $this->checkAndUpdatePetitionStatus($postId);

            $this->indexPost($postId);
            return $status;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function checkAndUpdatePetitionStatus($postId)
    {
        $statusQuery = "
		SELECT signatures, target_signatures, status
		FROM petitions
		WHERE post_id = ?";

        $statusStmt = $this->db->prepare($statusQuery);
        $statusStmt->execute([$postId]);
        $result = $statusStmt->fetch(\PDO::FETCH_ASSOC);

        if ($result['signatures'] >= $result['target_signatures'] && $result['status'] != 'submitted') {
            $updateStatusQuery = "
			UPDATE petitions
			SET status = 'submitted'
			WHERE post_id = ?";

            $updateStatusStmt = $this->db->prepare($updateStatusQuery);
            $updateStatusStmt->execute([$postId]);

            $result['status'] = 'submitted';
        }

        return $result['status'];
    }

    public function getPetitionSignees($postId, $page, $pageSize)
    {
        $offset = ($page - 1) * $pageSize;

        $query = "
		SELECT a.id, a.name, a.photo_url
		FROM petition_signatures ps
		JOIN accounts a ON ps.account_id = a.id
		WHERE ps.post_id = ?
		LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$postId, $pageSize, $offset]);
        $data = $stmt->fetchAll(\PDO::FETCH_CLASS);

        $countQuery = "
		SELECT COUNT(*) as total
		FROM petition_signatures ps
		WHERE ps.post_id = ?";

        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute([$postId]);
        $total = $countStmt->fetchColumn();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'current_page' => (int) $page,
                'last_page' => (int) ceil($total / $pageSize),
                'page_size' => (int) $pageSize,
            ],
        ];
    }

    public function getEyewitnessReportApprovals($postId, $page, $pageSize)
    {
        $offset = ($page - 1) * $pageSize;

        $query = "
		SELECT a.id, a.name, a.photo_url
		FROM eye_witness_reports_approvals ewra
		JOIN accounts a ON ewra.account_id = a.id
		WHERE ewra.post_id = ?
		LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$postId, $pageSize, $offset]);
        $data = $stmt->fetchAll(\PDO::FETCH_CLASS);

        $countQuery = "
		SELECT COUNT(*) as total
		FROM eye_witness_reports_approvals ewra
		WHERE ewra.post_id = ?";

        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute([$postId]);
        $total = $countStmt->fetchColumn();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'current_page' => (int) $page,
                'last_page' => (int) ceil($total / $pageSize),
                'page_size' => (int) $pageSize,
            ],
        ];
    }


    public function deletePost($postId)
    {
        $query = "DELETE FROM posts WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$postId]);

        app('search')->deleteData('posts', $postId);
    }

}
