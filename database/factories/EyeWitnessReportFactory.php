<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;

class EyeWitnessReportFactory
{
    protected $db;
    protected $title;
    protected $description;
    protected $creatorId;

    public function __construct($db = null, $data = [])
    {
        $this->db = $db ?: DB::connection()->getPdo();

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function createReport($data)
    {
        $query = "
        INSERT INTO eye_witness_reports (title, description, creator_id, category)
        VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([$data['title'], $data['description'], $data['creatorId'], $data['category']]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                throw new \Exception('A report with this title already exists.', 409);
            }
        }
    }

    public function getAllReports()
    {
        $query = "SELECT * FROM eye_witness_reports";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $query = "SELECT * FROM eye_witness_reports WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function hasUserApproved($reportId, $accountId)
    {
        $query = "
        SELECT COUNT(*)
        FROM eye_witness_reports_approvals
        WHERE report_id = ? AND account_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$reportId, $accountId]);
        return $stmt->fetchColumn() > 0;
    }

    public function insertApproval($reportId, $accountId, $comment = null)
    {
        try {
            $this->db->beginTransaction();

            $query = "
            INSERT INTO eye_witness_reports_approvals (report_id, account_id, approved_at)
            VALUES (?, ?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$reportId, $accountId, now()]);

            $incrementQuery = "
            UPDATE eye_witness_reports
            SET approvals = approvals + 1
            WHERE id = ?";

            $incrementStmt = $this->db->prepare($incrementQuery);
            $incrementStmt->execute([$reportId]);

            if ($comment) {
                $this->insertComment($reportId, $accountId, $comment);
            }

            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function insertComment($reportId, $accountId, $comment)
    {
        $query = "
        INSERT INTO eye_witness_reports_comments (report_id, account_id, comment, commented_at)
        VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$reportId, $accountId, $comment, now()]);
    }

    public function getFilteredReports(array $criteria)
    {
        $query = "SELECT * FROM eye_witness_reports WHERE 1=1";
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
            $query .= " AND type = ?";
            $params[] = $filter;
        }

        $allowedSortColumns = ['created_at', 'approvals', 'title'];
        $allowedSortOrders = ['asc', 'desc'];

        if (in_array($sortBy, $allowedSortColumns) && in_array($sortOrder, $allowedSortOrders)) {
            $query .= " ORDER BY {$sortBy} {$sortOrder}";
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
