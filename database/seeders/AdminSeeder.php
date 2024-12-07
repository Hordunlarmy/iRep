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

        $adminData = [
            [
                'username' => 'admin1',
                'password' => bcrypt('password'),
                'account_type' => 3
            ],
            [
                'username' => 'admin2',
                'password' => bcrypt('password'),
                'account_type' => 3
            ],
            [
                'username' => 'admin3',
                'password' => bcrypt('password'),
                'account_type' => 3
            ],
            [
                'username' => 'admin4',
                'password' => bcrypt('password'),
                'account_type' => 3
            ],
            [
                'username' => 'admin5',
                'password' => bcrypt('password'),
                'account_type' => 3
            ],
            [
                'username' => 'admin6',
                'password' => bcrypt('password'),
                'account_type' => 3
            ]
        ];

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
