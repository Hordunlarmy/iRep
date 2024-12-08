<?php

namespace App\Admin\Factories;

use Illuminate\Support\Facades\DB;
use App\Admin\Models\Admin;
use Illuminate\Support\Facades\Log;

/**
 * class for creating admin accounts
 */
class UserManagementFactory
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::connection()->getPdo();
    }

    public function getAccountStats($accountType)
    {
        $query = "
			SELECT
				COUNT(CASE WHEN a.account_type = :accountType THEN 1 END) AS total_accounts,
				COUNT(CASE WHEN a.kyced IS TRUE AND a.kyc IS NOT NULL THEN 1 END) AS verified_accounts,
				COUNT(CASE WHEN a.kyc IS NOT NULL AND a.kyced IS FALSE THEN 1 END) AS pending_verifications,
				COUNT(CASE WHEN a.status = 'suspended' THEN 1 END) AS suspended_accounts,
				(
					SELECT COUNT(*)
					FROM deleted_entities
					WHERE entity_type = 'account'
				) AS deleted_accounts
			FROM accounts AS a
		";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':accountType', $accountType, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAccounts($filter = [], $accountType)
    {
        $page = $filter['page'] ?? 1;
        $pageSize = $filter['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;

        $allowedFilters = ['verified', 'pending_verification', 'suspended'];

        $query = "
			SELECT a.id, a.name, a.email, s.name AS state,
			lg.name AS local_government,
			CASE
				 WHEN a.status = 'suspended' THEN 'suspended'
				 WHEN a.kyced IS TRUE THEN 'verified'
				 WHEN a.kyced IS FALSE AND a.kyc IS NOT NULL THEN 'unverified'
				 ELSE 'active'
			END AS status
		";

        if ($accountType == 2) {
            $query .= ", p.name AS party, pos.title AS position, c.name AS constituency, d.name AS district
				FROM accounts AS a
				LEFT JOIN states AS s ON a.state_id = s.id
				LEFT JOIN local_governments AS lg ON a.local_government_id = lg.id
                LEFT JOIN representatives AS r ON r.account_id = a.id
				LEFT JOIN parties AS p ON p.id = r.party_id
				LEFT JOIN positions AS pos ON pos.id = r.position_id
				LEFT JOIN constituencies AS c ON c.id = r.constituency_id
				LEFT JOIN districts AS d ON d.id = r.district_id
			";
        } else {
            $query .= " FROM accounts AS a
			LEFT JOIN states AS s ON a.state_id = s.id
			LEFT JOIN local_governments AS lg ON a.local_government_id = lg.id
			";
        }

        $query .= " WHERE a.account_type = :accountType";

        if (isset($filter['status']) && in_array($filter['status'], $allowedFilters)) {
            if ($filter['status'] == 'verified') {
                $query .= " AND a.kyced IS TRUE";
            } elseif ($filter['status'] == 'pending_verification') {
                $query .= " AND a.kyc IS NOT NULL AND a.kyced IS FALSE";
            } elseif ($filter['status'] == 'suspended') {
                $query .= " AND a.status = 'suspended'";
            }
        }

        $query .= " LIMIT :pageSize OFFSET :offset";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':accountType', $accountType, \PDO::PARAM_INT);
        $stmt->bindParam(':pageSize', $pageSize, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalRecords = $this->getAccountCount($filter, $accountType, $allowedFilters);
        $totalPages = ceil($totalRecords / $pageSize);

        return [
            'data' => $data,
            'meta' => [
                'current_page' => (int) $page,
                'page_size' => (int) $pageSize,
                'total_records' => $totalRecords,
                'total_pages' => $totalPages,
            ],
        ];
    }

    public function getAccountCount($filter = [], $accountType, $allowedFilters)
    {
        $query = "
			SELECT COUNT(*) as total
			FROM accounts AS a
		";

        $query .= " WHERE a.account_type = :accountType";

        if (isset($filter['status']) && in_array($filter['status'], $allowedFilters)) {
            if ($filter['status'] == 'verified') {
                $query .= " AND a.kyced IS TRUE";
            } elseif ($filter['status'] == 'pending_verification') {
                $query .= " AND a.kyc IS NOT NULL AND a.kyced IS FALSE";
            } elseif ($filter['status'] == 'suspended') {
                $query .= " AND a.status = 'suspended'";
            }
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':accountType', $accountType, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function approveAccount($accountId)
    {
        $query = "
			UPDATE accounts
			SET kyced = TRUE
			WHERE id = :accountId
		";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':accountId', $accountId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function disapproveAccount($accountId)
    {
        $query = "
			UPDATE accounts
			SET kyced = FALSE
			WHERE id = :accountId
		";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':accountId', $accountId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function suspendAccount($accountId)
    {
        $query = "
			UPDATE accounts
			SET status = 'suspended'
			WHERE id = :accountId
		";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':accountId', $accountId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function unsuspendAccount($accountId)
    {
        $query = "
			UPDATE accounts
			SET status = 'active'
			WHERE id = :accountId
		";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':accountId', $accountId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function deleteAccount($accountId)
    {
        $this->db->beginTransaction();

        try {
            $insertQuery = "
				INSERT INTO deleted_entities (entity_id, entity_type)
				VALUES (:accountId, 'account')
			";

            $insertStmt = $this->db->prepare($insertQuery);
            $insertStmt->bindParam(':accountId', $accountId, \PDO::PARAM_INT);
            $insertStmt->execute();

            $deleteQuery = "
				DELETE FROM accounts
				WHERE id = :accountId
			";

            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindParam(':accountId', $accountId, \PDO::PARAM_INT);
            $deleteStmt->execute();

            $this->db->commit();

            return $deleteStmt->rowCount();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

}
