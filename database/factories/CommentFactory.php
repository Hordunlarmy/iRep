<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;

class commentFactory
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
		SELECT *
		FROM comments
		WHERE comment_id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);

        return $stmt->fetch();
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
