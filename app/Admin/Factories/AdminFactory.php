<?php

namespace App\Admin\Factories;

use Illuminate\Support\Facades\DB;
use App\Admin\Models\Admin;
use Illuminate\Support\Facades\Log;

/**
 * class for creating admin accounts
 */
class AdminFactory
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::connection()->getPdo();
    }

    public function createAdmin(array $data)
    {
        $admin = new Admin(null, [
            'username' => $data['username'],
            'password' => $data['password'],
            'permissions' => $data['permissions'] ?? [],
            'account_type' => 3,
        ]);

        $result = $admin->insertAdmin();

        return $result;
    }

    public function createSuperAdmin(array $data)
    {
        $permissions = $this->getAdminPermissions();

        $permissionIds = array_column($permissions, 'permission_id');

        $admin = new Admin(null, [
            'username' => $data['username'],
            'password' => $data['password'],
            'permissions' => $permissionIds,
            'account_type' => 4,
        ]);

        $result = $admin->insertAdmin();

        return $result;
    }

    public function getAdminPermissions(): array
    {
        $query = "
		SELECT *
		FROM permissions";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAdmin(string $username = null, int $id = null): ?Admin
    {
        $query = "
        SELECT *
        FROM admins
        WHERE username = ? OR id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username, $id]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new Admin($this->db, $data);
    }

    public function getAdmins($filter = [])
    {
        $stmt = $this->db->query("SELECT name FROM permissions");
        $availablePermissions = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $query = "
		SELECT
			a.id,
			a.username,
			a.photo_url,
			a.account_type,
			COALESCE(
				JSON_ARRAYAGG(
					DISTINCT JSON_OBJECT('id', p.id, 'name', p.name)
				),
				JSON_ARRAY()
			) AS permissions
		FROM admins a
		LEFT JOIN admin_permissions ap ON a.id = ap.admin_id
		LEFT JOIN permissions p ON ap.permission_id = p.id
		WHERE a.account_type = 3
		AND a.id NOT IN (SELECT entity_id FROM deleted_entities)";

        $params = [];

        if (isset($filter['account_type'])) {
            $query .= " AND a.account_type = ?";
            $params[] = $filter['account_type'];
        }

        $permissionFilters = [];
        if (!empty($filter['permission']) && in_array($filter['permission'], $availablePermissions)) {
            $permissionFilters[] = $filter['permission'];
        }

        if (!empty($permissionFilters)) {
            $placeholders = implode(', ', array_fill(0, count($permissionFilters), '?'));
            $query .= " AND p.name IN ($placeholders)";
            $params = array_merge($params, $permissionFilters);
        }

        $query .= " GROUP BY a.id, a.photo_url, a.username, a.password, a.account_type";

        $page = $filter['page'] ?? 1;
        $pageSize = $filter['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;

        $query .= " LIMIT ? OFFSET ?";
        $params[] = $pageSize;
        $params[] = $offset;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $admins = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($admins as &$admin) {
            $permissionsQuery = "
            SELECT p.id, p.name
            FROM permissions p
            LEFT JOIN admin_permissions ap ON p.id = ap.permission_id
            WHERE ap.admin_id = ?";
            $permissionsStmt = $this->db->prepare($permissionsQuery);
            $permissionsStmt->execute([$admin['id']]);
            $admin['permissions'] = $permissionsStmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $totalRecords = $this->getTotalAdminsCount($filter, $permissionFilters);
        return [
            'data' => $admins,
            'meta' => [
                'current_page' => (int) $page,
                'page_size' => (int) $pageSize,
                'total_records' => $totalRecords,
                'total_pages' => ceil($totalRecords / $pageSize),
            ],
        ];
    }

    private function getTotalAdminsCount($filter, $permissionFilters)
    {
        $countQuery = "
		SELECT COUNT(DISTINCT a.id)
		FROM admins a
		LEFT JOIN admin_permissions ap ON a.id = ap.admin_id
		LEFT JOIN permissions p ON ap.permission_id = p.id
		WHERE a.account_type = 3
		AND a.id NOT IN (SELECT entity_id FROM deleted_entities)";

        if (isset($filter['account_type'])) {
            $countQuery .= " AND a.account_type = ?";
        }

        if (!empty($permissionFilters)) {
            $placeholders = implode(', ', array_fill(0, count($permissionFilters), '?'));
            $countQuery .= " AND p.name IN ($placeholders)";
        }

        $params = $permissionFilters;

        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($params);
        return $countStmt->fetchColumn();
    }

    public function getAdminCounts()
    {
        $stmt = $this->db->query("SELECT name FROM permissions");
        $permissions = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($permissions)) {
            return [];
        }

        $selectQueries = [];
        $params = [];

        // General count of all admins
        $selectQueries[] = "COUNT(DISTINCT a.id) AS all_admins";

        // Dynamically add counts for each permission
        foreach ($permissions as $permission) {
            $permissionAlias = str_replace(' ', '_', $permission);
            $selectQueries[] = "
			CAST(SUM(CASE WHEN p.name = ? THEN 1 ELSE 0 END) AS SIGNED) AS `{$permissionAlias}`";
            $params[] = $permission;
        }

        // Add count of deleted admins
        $selectQueries[] = "
			(SELECT COUNT(*)
			 FROM deleted_entities
			 WHERE deleted_entities.entity_type = 'admin') AS deleted_admins
		";
        // Construct the full SQL query
        $query = "
			SELECT
				" . implode(', ', $selectQueries) . "
			FROM admins a
			LEFT JOIN admin_permissions ap ON a.id = ap.admin_id
			LEFT JOIN permissions p ON ap.permission_id = p.id
			WHERE a.account_type = 3
		";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        $result = $stmt->fetch(\PDO::FETCH_OBJ);

        return $result;
    }

}
