<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $availablePermissions = [1, 2, 3, 4];

        $permissionsData = [
            [ 'name' => 'content mod' ],
            [ 'name' => 'petitions' ],
            [ 'name' => 'user verification' ],
            [ 'name' => 'rep verification' ],
        ];

        foreach ($permissionsData as $permission) {
            try {
                DB::table('permissions')->insert($permission);
            } catch (\Exception $e) {
                Log::error('Failed to insert permission: ' . $e->getMessage());
            }
        }

        $adminData = [];
        for ($i = 1; $i <= 7; $i++) {
            $adminData[] = [
                'username' => 'admin' . $i,
                'password' => bcrypt('password'),
                'account_type' => 3
            ];
        }

        foreach ($adminData as $admin) {
            try {
                $adminId = DB::table('admins')->insertGetId($admin);

                $randomPermissions = array_rand($availablePermissions, rand(1, 3)); // Randomly select 1-3 permissions
                $randomPermissions = is_array($randomPermissions) ? $randomPermissions : [$randomPermissions];

                $permissionsData = [];
                foreach ($randomPermissions as $permissionId) {
                    $permissionsData[] = ['admin_id' => $adminId, 'permission_id' => $availablePermissions[$permissionId]];
                }

                DB::table('admin_permissions')->insert($permissionsData);

            } catch (\Exception $e) {
                Log::error('Failed to insert admin: ' . $e->getMessage(), ['admin' => $admin]);
            }
        }
    }
}
