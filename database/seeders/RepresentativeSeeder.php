<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            $constituency = trim($row[2]);
            $party = trim($row[3]);
            $email = trim($row[4]);
            $phone_number = trim($row[5]);

            $phone_number = ($phone_number === 'N/A' || empty($phone_number)) ? null : $phone_number;

            if (empty($name)) {
                return;
            }

            if (empty($email)) {
                $email = Str::random(10) . '@example.com';
            } elseif (DB::table('accounts')->where('email', $email)->exists()) {
                continue;
            }

            $position = trim($row[6]);

            // Insert account data
            $account_id = DB::table('accounts')->insertGetId([
                'photo_url' => null,
                'name' => $name,
                'email' => $email,
                'phone_number' => $phone_number,
                'dob' => null,
                'state' => $state,
                'local_government' => null,
                'polling_unit' => null,
                'password' => Hash::make('password'),
                'email_verified' => true,
                'account_type' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert representative data
            DB::table('representatives')->insert([
                'position' => $position,
                'constituency' => $constituency,
                'party' => $party,
                'bio' => $name . ' is a representative from ' . $constituency,
                'account_id' => $account_id,
            ]);
        }
    }
}
