<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RepresentativeSeeder extends Seeder
{
    public function run()
    {
        $filePath = storage_path('app/lawmakers.xlsx');

        // Load data from the spreadsheet
        $data = Excel::toCollection(null, $filePath)->first();

        foreach ($data as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $name = trim($row[0]);
            $state = trim($row[1]);
            $district = trim($row[2]);
            $party = trim($row[3]);
            $email = trim($row[4]);
            $phone_number = trim($row[5]);
            $position = trim($row[6]);

            $phone_number = ($phone_number === 'N/A' || empty($phone_number)) ? null : $phone_number;

            if (empty($name)) {
                continue;
            }

            if (empty($email)) {
                $email = Str::random(10) . '@example.com';
            } elseif (DB::table('accounts')->where('email', $email)->exists()) {
                continue;
            }

            try {
                $position_id = DB::table('positions')->where('title', $position)->value('id');
                $party_id = DB::table('parties')->where('code', $party)->value('id');
                $state_id = DB::table('states')->where('name', $state)->value('id');
                $district_id = DB::table('districts')->where('name', $district)->value('id');

                // Insert account data
                $account_id = DB::table('accounts')->insertGetId([
                    'photo_url' => "https://i.imgur.com/0GY9tnz.jpeg",
                    'name' => $name,
                    'email' => $email,
                    'phone_number' => $phone_number,
                    'dob' => null,
                    'state_id' => $state_id ?? null,
                    'local_government_id' => null,
                    'polling_unit' => null,
                    'password' => Hash::make('password456'),
                    'email_verified' => true,
                    'account_type' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert representative data
                DB::table('representatives')->insert([
                    'position_id' => $position_id ?? null,
                    'constituency_id' => $constituency_id ?? null,
                    'district_id' => $district_id ?? null,
                    'party_id' => $party_id ?? null,
                    'bio' => $name . ' is a representative from ' . $district,
                    'account_id' => $account_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to insert representative: ' . $e->getMessage(), ['row' => $row]);
            }
        }
    }
}
