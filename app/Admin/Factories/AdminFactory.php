<?php

namespace App\Admin\Factories;

use Illuminate\Support\Facades\DB;
use App\Admin\Models\Admin;

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

    public function getAdmin(string $username = null, int $id = null): Admin
    {
        $query = "
		SELECT *
		FROM admins
		WHERE username = ? OR id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username, $id]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new Admin($this->db, $data);
    }
}
