<?php

namespace App\Admin\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class Admin extends Authenticatable implements JWTSubject
{
    protected $db;
    public $id;
    public $username;
    public $password;
    public $account_type;
    public $permissions = [];

    public function __construct($db, $data)
    {
        $this->db = $db ?: DB::connection()->getPdo();

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function insertAdmin(): int
    {
        $query = "
        INSERT INTO admins (username, password, account_type)
        VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $this->username,
            Hash::make($this->password),
            $this->account_type,
        ]);

        $admin_id = $this->db->lastInsertId();

        if (!empty($this->permissions)) {
            $this->insertAdminPermissions($admin_id, $this->permissions);
        }

        return $admin_id;
    }

    protected function insertAdminPermissions($admin_id, $permissions): void
    {
        $query = "
        INSERT INTO admin_permissions (admin_id, permission_id)
        VALUES (?, ?)";
        $stmt = $this->db->prepare($query);

        foreach ($permissions as $permission) {
            $stmt->execute([$admin_id, $permission]);
        }
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->id;
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'account_type' => $this->account_type,
        ];
    }
}
