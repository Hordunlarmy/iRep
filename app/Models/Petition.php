<?php

namespace App\Models;

class Petition
{
    protected $postId;
    protected $targetRepresentativeId;
    protected $signatures;

    public function __construct($postId, $data)
    {
        $this->postId = $postId;
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function insert($db)
    {
        $query = "
		INSERT INTO petitions (post_id, target_representative_id)
		VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$this->postId, $this->targetRepresentativeId]);

        return $this->postId;
    }
}
