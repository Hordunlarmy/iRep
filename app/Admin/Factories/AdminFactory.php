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

    public function getAdminPermissionsByUsername(string $username): array
    {
        $query = "
        SELECT p.id
        FROM permissions p
        JOIN admin_permissions ap ON p.id = ap.permission_id
        JOIN admins a ON ap.admin_id = a.id
        WHERE a.username = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username]);

        $permissions = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $permissions;
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

    public function getActivity(int $activityId)
    {
        $query = "
        SELECT
        aa.id,
        aa.admin_id,
        aa.entity_type,
        aa.entity_id,
        aa.action,
        aa.description,
        aa.created_at,
        a.username AS admin_username,
        a.photo_url AS admin_photo,
        e.username AS entity_username,
        e.photo_url AS entity_photo,
        CASE
            WHEN aa.entity_type = 'account' THEN JSON_OBJECT(
                'email_verified', ac.email_verified,
                'phone_verified', ac.phone_verified,
                'account_type', ac.account_type
            )
            WHEN aa.entity_type = 'post' THEN JSON_OBJECT(
                'title', p.title,
                'context', p.context,
                'status', p.status
            )
            WHEN aa.entity_type = 'comment' THEN JSON_OBJECT(
                'content', c.content,
                'status', c.status
            )
        END AS entity_data
        FROM admin_activities aa
        LEFT JOIN admins a ON aa.admin_id = a.id
        LEFT JOIN accounts e ON aa.entity_id = e.id AND aa.entity_type = 'account'
        LEFT JOIN posts p ON aa.entity_id = p.id AND aa.entity_type = 'post'
        LEFT JOIN comments c ON aa.entity_id = c.id AND aa.entity_type = 'comment'
        LEFT JOIN accounts ac ON e.id = ac.id
        WHERE aa.id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$activityId]);

        return $stmt->fetchObject();
    }


}
