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

        // Additional hardcoded seed data for specified positions
        $positions = [
            'President' => [
                ['name' => 'Chris Adams',
                'state' => 'Lagos', 'district' => 'Ikeja', 'party' => 'PDP', 'email' => 'chris.adams@example.com', 'phone_number' => '08012345678'],
                ['name' => 'Patricia Olamide', 'state' => 'Ogun', 'district' => 'Abeokuta', 'party' => 'APC', 'email' => 'patricia.olamide@example.com', 'phone_number' => '08123456789'],
            ],
            'Vice President' => [
                ['name' => 'Michael Abiola', 'state' => 'Abuja', 'district' => 'Central', 'party' => 'APC', 'email' => 'michael.abiola@example.com', 'phone_number' => '08098765432'],
                ['name' => 'Sarah Oni', 'state' => 'Rivers', 'district' => 'Port Harcourt', 'party' => 'PDP', 'email' => 'sarah.oni@example.com', 'phone_number' => '09012345678'],
            ],
            'Minister' => [
                ['name' => 'Kehinde Adebayo', 'state' => 'Ekiti', 'district' => 'Ado Ekiti', 'party' => 'APC', 'email' => 'kehinde.adebayo@example.com', 'phone_number' => '08123456789'],
                ['name' => 'Titi Johnson', 'state' => 'Delta', 'district' => 'Asaba', 'party' => 'PDP', 'email' => 'titi.johnson@example.com', 'phone_number' => '08012345678'],
            ],
            'Senator' => [
                ['name' => 'Jibola Adebayo', 'state' => 'Osun', 'district' => 'Osogbo', 'party' => 'PDP', 'email' => 'jibola.adebayo@example.com', 'phone_number' => '09011223344'],
                ['name' => 'Grace Afolabi', 'state' => 'Ogun', 'district' => 'Abeokuta', 'party' => 'APC', 'email' => 'grace.afolabi@example.com', 'phone_number' => '08098765432'],
            ],
            'House of Representatives Member' => [
                ['name' => 'Solomon Okoro', 'state' => 'Lagos', 'district' => 'Oshodi-Isolo', 'party' => 'APC', 'email' => 'solomon.okoro@example.com', 'phone_number' => '09023456789'],
                ['name' => 'Bukola Adewumi', 'state' => 'Oyo', 'district' => 'Ibadan North', 'party' => 'PDP', 'email' => 'bukola.adewumi@example.com', 'phone_number' => '08012345678'],
            ],
            'Governor' => [
                ['name' => 'Tunde Babajide', 'state' => 'Ekiti', 'district' => 'Ado Ekiti', 'party' => 'APC', 'email' => 'tunde.babajide@example.com', 'phone_number' => '08012345678'],
                ['name' => 'Ruth Afolabi', 'state' => 'Lagos', 'district' => 'Ikeja', 'party' => 'PDP', 'email' => 'ruth.afolabi@example.com', 'phone_number' => '09087654321'],
            ],
            'Deputy Governor' => [
                ['name' => 'Ibrahim Olumide', 'state' => 'Kwara', 'district' => 'Ilorin', 'party' => 'APC', 'email' => 'ibrahim.olumide@example.com', 'phone_number' => '09012345678'],
                ['name' => 'Ngozi Osuji', 'state' => 'Anambra', 'district' => 'Awka', 'party' => 'PDP', 'email' => 'ngozi.osuji@example.com', 'phone_number' => '08023456789'],
            ],
            'State House of Assembly Member' => [
                ['name' => 'Oluwaseun Akinwunmi', 'state' => 'Lagos', 'district' => 'Oshodi-Isolo', 'party' => 'APC', 'email' => 'oluwaseun.akinwunmi@example.com', 'phone_number' => '08012345678'],
                ['name' => 'Chidimma Uche', 'state' => 'Enugu', 'district' => 'Enugu North', 'party' => 'PDP', 'email' => 'chidimma.uche@example.com', 'phone_number' => '09098765432'],
            ],
            'Chairman (LGA)' => [
                ['name' => 'Jide Babajide', 'state' => 'Ogun', 'district' => 'Abeokuta', 'party' => 'PDP', 'email' => 'jide.babajide@example.com', 'phone_number' => '08123456789'],
                ['name' => 'Ebere Nwachukwu', 'state' => 'Abuja', 'district' => 'Bwari', 'party' => 'APC', 'email' => 'ebere.nwachukwu@example.com', 'phone_number' => '09087654321'],
            ],
            'Vice Chairman (LGA)' => [
                ['name' => 'Emeka Okafor', 'state' => 'Abuja', 'district' => 'Abuja Municipal', 'party' => 'APC', 'email' => 'emeka.okafor@example.com', 'phone_number' => '08023456789'],
                ['name' => 'Oluwatoyin Akinwunmi', 'state' => 'Lagos', 'district' => 'Surulere', 'party' => 'PDP', 'email' => 'oluwatoyin.akinwunmi@example.com', 'phone_number' => '09012345678'],
            ],
            'Councillor' => [
                ['name' => 'Tunde Adeola', 'state' => 'Ogun', 'district' => 'Ifo', 'party' => 'APC', 'email' => 'tunde.adeola@example.com', 'phone_number' => '08012345678'],
                ['name' => 'Micheal Afolabi', 'state' => 'Oyo', 'district' => 'Ibadan', 'party' => 'PDP', 'email' => 'micheal.afolabi@example.com', 'phone_number' => '09098765432'],
            ],
            'Special Adviser' => [
                ['name' => 'Rita Okoro', 'state' => 'Lagos', 'district' => 'Surulere', 'party' => 'APC', 'email' => 'rita.okoro@example.com', 'phone_number' => '09012345678'],
                ['name' => 'Chigozie Okwara', 'state' => 'Abia', 'district' => 'Umuahia', 'party' => 'PDP', 'email' => 'chigozie.okwara@example.com', 'phone_number' => '08023456789'],
            ],
        ];

        foreach ($positions as $positionTitle => $representatives) {
            foreach ($representatives as $representative) {
                try {
                    $position_id = DB::table('positions')->where('title', $positionTitle)->value('id');
                    $party_id = DB::table('parties')->where('code', $representative['party'])->value('id');
                    $state_id = DB::table('states')->where('name', $representative['state'])->value('id');
                    $district_id = DB::table('districts')->where('name', $representative['district'])->value('id');

                    $account_id = DB::table('accounts')->insertGetId([
                        'photo_url' => "https://i.imgur.com/0GY9tnz.jpeg",
                        'name' => $representative['name'],
                        'email' => $representative['email'],
                        'phone_number' => $representative['phone_number'],
                        'dob' => null,
                        'state_id' => $state_id ?? null,
                        'local_government_id' => null,
                        'polling_unit' => null,
                        'password' => Hash::make('password456'),
                        'email_verified' => true,
                        'account_type' => 2,
                    ]);

                    DB::table('representatives')->insert([
                        'position_id' => $position_id ?? null,
                        'constituency_id' => $constituency_id ?? null,
                        'district_id' => $district_id ?? null,
                        'party_id' => $party_id ?? null,
                        'bio' => $representative['name'] . ' is a representative from ' . $representative['district'],
                        'account_id' => $account_id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to insert representative: ' . $e->getMessage(), ['representative' => $representative]);
                }
            }
        }
    }
}
