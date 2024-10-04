<?php

namespace app\models;

use Illuminate\Support\Facades\DB;

class Petition
{
    protected $db;
    protected $title;
    protected $description;
    protected $creatorId;
    protected $targetRepresentativeId;

    public function __construct($db = null, $data = [])
    {
        $this->db = $db ?: DB::connection()->getPdo();

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function createPetition($data)
    {
        $query = "
        INSERT INTO petitions (title, description, creator_id, target_representative_id)
        VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([$data['title'], $data['description'], $data['creatorId'], $data['target_representative_id']]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                throw new \Exception('A petition with this title already exists.', 409);
            }
        }
    }

    public function getAllPetitions()
    {
        $query = "SELECT * FROM petitions";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $query = "SELECT * FROM petitions WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function hasUserSigned($petitionId, $accountId)
    {
        $query = "
        SELECT COUNT(*)
        FROM petition_signatures
        WHERE petition_id = ? AND account_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$petitionId, $accountId]);
        return $stmt->fetchColumn() > 0;
    }

    public function insertSignature($petitionId, $accountId, $comment = null)
    {
        $query = "
        INSERT INTO petition_signatures (petition_id, account_id, signed_at)
        VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$petitionId, $accountId, now()]);

        if ($comment) {
            $this->insertComment($petitionId, $accountId, $comment);
        }
    }

    public function insertComment($petitionId, $accountId, $comment)
    {
        $query = "
        INSERT INTO petition_comments (petition_id, account_id, comment, commented_at)
        VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$petitionId, $accountId, $comment, now()]);
    }


    public function incrementSignatureCount($id)
    {
        $query = "
        UPDATE petitions
        SET signature_count = signature_count + 1
        WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
    }
}
