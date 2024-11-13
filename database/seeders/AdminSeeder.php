<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
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

        DB::table('accounts')->insert($adminData);
    }
}
