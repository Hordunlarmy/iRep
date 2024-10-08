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

}
