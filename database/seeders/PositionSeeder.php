<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $positionsData = [
            // Federal Positions
            ['title' => 'President'],
            ['title' => 'Vice President'],
            ['title' => 'Minister'],
            ['title' => 'Senator'],
            ['title' => 'House of Representatives Member'],

            // State Positions
            ['title' => 'Governor'],
            ['title' => 'Deputy Governor'],
            ['title' => 'State House of Assembly Member'],

            // Local Government Positions
            ['title' => 'Chairman (LGA)'],
            ['title' => 'Vice Chairman (LGA)'],
            ['title' => 'Councillor'],

            // Other Positions
            ['title' => 'Special Adviser'],
            ['title' => 'Local Government Secretary'],
            ['title' => 'Political Party Chairman'],
            ['title' => 'National Chairman (Party)'],
            ['title' => 'Secretary to the Government'],
        ];

        foreach ($positionsData as $position) {
            try {
                DB::table('positions')->insert($position);
            } catch (\Exception $e) {
                Log::error('Failed to insert position data: ' . $e->getMessage());
            }
        }
    }
}
