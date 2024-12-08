<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deletedAdmins = [
            [
                'entity_id' => 6,
                'entity_type' => 'admin',
            ],
            [
                'entity_id' => 7,
                'entity_type' => 'admin',
            ]
        ];

        DB::table('deleted_entities')->insert($deletedAdmins);

        $adminActivities = [
            [
                'admin_id' => 1,
                'activity_type' => 'login',
                'description' => 'Admin logged in',
            ],
            [
                'admin_id' => 1,
                'activity_type' => 'update',
                'description' => 'Updated user profile',
            ],
            [
                'admin_id' => 2,
                'activity_type' => 'delete',
                'description' => 'Deleted user account with ID 10',
            ]
        ];

        DB::table('admin_activities')->insert($adminActivities);
    }
}
