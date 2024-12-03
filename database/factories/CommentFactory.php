<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;

class CommentFactory
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::connection()->getPdo();
    }

    public function insertComment($data)
    {
        $query = "
		INSERT INTO comments
		(post_id, account_id, comment, parent_id)
		VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $data['postId'],
            $data['accountId'],
            $data['comment'],
            $data['parentId'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    public function getComment($id)
    {
        $query = "
		SELECT comments.*, accounts.id AS author_id,
		accounts.photo_url AS author_photo_url, accounts.name AS author_name
        FROM comments
        LEFT JOIN accounts ON comments.account_id = accounts.id
        WHERE comments.id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function getCommentsByUser($accountId)
    {
        $query = "
        SELECT comments.*,
               accounts.id AS author_id,
               accounts.photo_url AS author_photo_url,
               accounts.name AS author_name
        FROM comments
        LEFT JOIN accounts ON comments.account_id = accounts.id
        WHERE comments.account_id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$accountId]);

        return $stmt->fetchAll();
    }

    public function toggleAction($table, $postId, $accountId)
    {
        $status = null;

        if ($this->hasUserAction($table, $postId, $accountId)) {
            $deleteQuery = "
			DELETE FROM {$table}
			WHERE entity_id = ? AND account_id = ?";
            $stmt = $this->db->prepare($deleteQuery);
            $stmt->execute([$postId, $accountId]);
            $status = 'removed';
        } else {
            $insertQuery = "
			INSERT INTO {$table} (entity_type, entity_id, account_id)
			VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($insertQuery);
            $stmt->execute(['post', $postId, $accountId]);
            $status = 'added';
        }

        return $status;
    }

    public function hasUserAction($table, $postId, $accountId)
    {
        $query = "
		SELECT COUNT(*)
		FROM {$table}
		WHERE entity_id = ? AND account_id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$postId, $accountId]);

        return $stmt->fetchColumn() > 0;
    }


}
