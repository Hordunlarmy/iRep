<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionsData = [
            [ 'name' => 'content mod' ],
            [ 'name' => 'petitions' ],
            [ 'name' => 'user verification' ],
            [ 'name' => 'rep verification'],
        ];

        foreach ($permissionsData as $permission) {
            try {
                DB::table('permissions')->insert($permission);
            } catch (\Exception $e) {
                Log::error('Failed to insert permission: ' . $e->getMessage());
            }
        }
    }
}
