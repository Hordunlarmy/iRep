<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed default admin user
        $adminData = [
            [
                'name' => 'iREP',
                'email' => 'news@irep.com',
                'password' => bcrypt('irep6565'),
                'photo_url' => 'https://i.imgur.com/HH3yXoK.png',
                'account_type' => 3,
                'email_verified' => true
            ]
        ];

        foreach ($adminData as $admin) {
            try {
                DB::table('accounts')->insert($admin);
            } catch (\Exception $e) {
                Log::error('Failed to insert admin: ' . $e->getMessage(), $admin);
            }
        }
    }
}
