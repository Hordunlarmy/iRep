<?php

namespace App\Models;

class EyeWitnessReport
{
    protected $postId;
    protected $category;

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
		INSERT INTO eye_witness_reports (post_id, category)
		VALUES (?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$this->postId, $this->category]);

        return $this->postId;
    }

}
