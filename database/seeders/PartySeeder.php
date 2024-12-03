<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $partyData = [
            ['name' => 'Accord', 'code' => 'A'],
            ['name' => 'Action Alliance', 'code' => 'AA'],
            ['name' => 'Action Democratic Party', 'code' => 'ADP'],
            ['name' => 'Action Peoples Party', 'code' => 'APP'],
            ['name' => 'African Action Congress', 'code' => 'AAC'],
            ['name' => 'African Democratic Congress', 'code' => 'ADC'],
            ['name' => 'All Progressive Congress', 'code' => 'APC'],
            ['name' => 'All Progressive Grand Alliance', 'code' => 'APGA'],
            ['name' => 'Allied Peoples Movement', 'code' => 'APM'],
            ['name' => 'Boot Party', 'code' => 'BP'],
            ['name' => 'Labour Party', 'code' => 'LP'],
            ['name' => 'National Rescue Movement', 'code' => 'NRM'],
            ['name' => 'New Nigeria Peoples Party', 'code' => 'NNPP'],
            ['name' => 'Peoples Democratic Party', 'code' => 'PDP'],
            ['name' => 'Peoples Redemption Party', 'code' => 'PRP'],
            ['name' => 'Social Democratic Party', 'code' => 'SDP'],
            ['name' => 'Young Progressives Party', 'code' => 'YPP'],
            ['name' => 'Zenith Labour Party', 'code' => 'ZLP'],
        ];

        foreach ($partyData as $party) {
            try {
                DB::table('parties')->insert($party);
            } catch (\Exception $e) {
                Log::error('Failed to insert party data: ' . $e->getMessage());
            }
        }
    }

}
