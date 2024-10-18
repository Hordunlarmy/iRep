<?php

namespace App\Models;

class Petition
{
    protected $postId;
    protected $targetSignatures;
    protected $targetRepresentativeId;
    protected $signatures;

    public function __construct($postId, $data)
    {
        \Log::info("post_data", $data);

        $this->postId = $postId;
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'target_signatures':
                    $this->targetSignatures = $value;
                    break;
                case 'target_representative':
                    $this->targetRepresentativeId = $value;
                    break;
                default:
                    if (property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                    break;
            }
        }
    }
    public function insert($db)
    {
        $query = "
		INSERT INTO petitions
		(post_id, target_representative_id, target_signatures)
		VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $this->postId,
            $this->targetRepresentativeId,
            $this->targetSignatures
        ]);

        return $this->postId;
    }
}
