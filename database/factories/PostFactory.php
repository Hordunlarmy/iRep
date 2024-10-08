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

    public function getPosts($limit = 10)
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
        ORDER BY p.created_at DESC
        LIMIT ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);

        return $stmt->fetchAll(\PDO::FETCH_CLASS);
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
