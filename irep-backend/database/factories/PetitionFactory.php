<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;

class PetitionFactory
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
        try {
            $this->db->beginTransaction();
            $query = "
            INSERT INTO petition_signatures (petition_id, account_id, signed_at)
            VALUES (?, ?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$petitionId, $accountId, now()]);

            $incrementQuery = "
            UPDATE petitions
            SET signatures = signatures + 1
            WHERE id = ?";

            $incrementStmt = $this->db->prepare($incrementQuery);
            $incrementStmt->execute([$petitionId]);

            if ($comment) {
                $this->insertComment($petitionId, $accountId, $comment);
            }

            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
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


    public function getFilteredPetitions(array $criteria)
    {
        $query = "SELECT * FROM petitions WHERE 1=1";
        $params = [];

        $search = $criteria['search'] ?? null;
        $filter = $criteria['filter'] ?? null;
        $sortBy = $criteria['sort_by'] ?? 'created_at';
        $sortOrder = $criteria['sort_order'] ?? 'desc';

        if (!empty($search)) {
            $query .= " AND (title LIKE ? OR description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filter)) {
            $query .= " AND status = ?";
            $params[] = $filter;
        }

        $allowedSortColumns = ['created_at', 'signature_count', 'title'];
        $allowedSortOrders = ['asc', 'desc'];

        if (in_array($sortBy, $allowedSortColumns) && in_array($sortOrder, $allowedSortOrders)) {
            $query .= " ORDER BY {$sortBy} {$sortOrder}";
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

}
