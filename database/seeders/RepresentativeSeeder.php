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

        // Process spreadsheet data
        foreach ($data as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $this->insertRepresentativeData($row);
        }

        // Additional hardcoded seed data
        $hardcodedRepresentatives = [
            // President
            [
                'Muhammadu Buhari', 'Katsina', 'Katsina', 'APC',
                'muhammadu.buhari@example.com', '08012345678', 'President',
            ],

            // Vice President
            [
                'Kashim Shettima', 'Borno', 'Maiduguri', 'APC',
                'kashim.shettima@example.com', '08023456789', 'Vice President',
            ],
            [
                'Atiku Abubakar', 'Adamawa', 'Yola', 'PDP',
                'atiku.abubakar@example.com', '08034567890', 'Vice President',
            ],

            // Minister
            [
                'Rotimi Amaechi', 'Rivers', 'Port Harcourt', 'APC',
                'rotimi.amaechi@example.com', '08045678901', 'Minister',
            ],
            [
                'Ngozi Okonjo-Iweala', 'Delta', 'Warri', 'PDP',
                'ngozi.okonjo-iweala@example.com', '08056789012', 'Minister',
            ],

            // Senator
            [
                'Bukola Saraki', 'Kwara', 'Ilorin', 'PDP',
                'bukola.saraki@example.com', '08067890123', 'Senator',
            ],
            [
                'Ahmed Lawan', 'Yobe', 'Damaturu', 'APC',
                'ahmed.lawan@example.com', '08078901234', 'Senator',
            ],

            // House of Representatives Member
            [
                'Femi Gbajabiamila', 'Lagos', 'Surulere', 'APC',
                'femi.gbajabiamila@example.com', '08089012345', 'House of Representatives Member',
            ],
            [
                'Rita Orji', 'Abia', 'Aba', 'PDP',
                'rita.orji@example.com', '08090123456', 'House of Representatives Member',
            ],

            // Governor
            [
                'Babajide Sanwo-Olu', 'Lagos', 'Lagos Island', 'APC',
                'babajide.sanwo-olu@example.com', '08001234567', 'Governor',
            ],
            [
                'Udom Emmanuel', 'Akwa Ibom', 'Uyo', 'PDP',
                'udom.emmanuel@example.com', '08012345678', 'Governor',
            ],

            // Deputy Governor
            [
                'Obafemi Hamzat', 'Lagos', 'Ikeja', 'APC',
                'obafemi.hamzat@example.com', '08023456789', 'Deputy Governor',
            ],
            [
                'Moses Ekpo', 'Akwa Ibom', 'Uyo', 'PDP',
                'moses.ekpo@example.com', '08034567890', 'Deputy Governor',
            ],

            // State House of Assembly Member
            [
                'Mudashiru Obasa', 'Lagos', 'Agege', 'APC',
                'mudashiru.obasa@example.com', '08045678901', 'State House of Assembly Member',
            ],
            [
                'Kingsley Esiso', 'Delta', 'Asaba', 'PDP',
                'kingsley.esiso@example.com', '08056789012', 'State House of Assembly Member',
            ],

            // Chairman (LGA)
            [
                'Olufunso Adeyemi', 'Ogun', 'Abeokuta', 'APC',
                'olufunso.adeyemi@example.com', '08067890123', 'Chairman (LGA)',
            ],
            [
                'Iretiola Akinwunmi', 'Ekiti', 'Ado Ekiti', 'PDP',
                'iretiola.akinwunmi@example.com', '08078901234', 'Chairman (LGA)',
            ],

            // Vice Chairman (LGA)
            [
                'Bola Abisoye', 'Ogun', 'Ota', 'APC',
                'bola.abisoye@example.com', '08089012345', 'Vice Chairman (LGA)',
            ],
            [
                'Chika Nwankwo', 'Enugu', 'Enugu North', 'PDP',
                'chika.nwankwo@example.com', '08090123456', 'Vice Chairman (LGA)',
            ],

            // Councillor
            [
                'Taiwo Akinola', 'Ogun', 'Ifo', 'APC',
                'taiwo.akinola@example.com', '08001234567', 'Councillor',
            ],
            [
                'Cynthia Ogbulafor', 'Abia', 'Aba South', 'PDP',
                'cynthia.ogbulafor@example.com', '08012345678', 'Councillor',
            ],

            // Special Adviser
            [
                'Dapo Olanipekun', 'Ogun', 'Abeokuta North', 'APC',
                'dapo.olanipekun@example.com', '08023456789', 'Special Adviser',
            ],
            [
                'Patricia Oboh', 'Edo', 'Benin City', 'PDP',
                'patricia.oboh@example.com', '08034567890', 'Special Adviser',
            ],

            // Local Government Secretary
            [
                'Samson Akintoye', 'Ogun', 'Abeokuta South', 'APC',
                'samson.akintoye@example.com', '08045678901', 'Local Government Secretary',
            ],
            [
                'Victoria Okeke', 'Anambra', 'Awka', 'PDP',
                'victoria.okeke@example.com', '08056789012', 'Local Government Secretary',
            ],

            // Political Party Chairman
            [
                'Adams Oshiomhole', 'Edo', 'Benin City', 'APC',
                'adams.oshiomhole@example.com', '08067890123', 'Political Party Chairman',
            ],
            [
                'Uche Secondus', 'Rivers', 'Port Harcourt', 'PDP',
                'uche.secondus@example.com', '08078901234', 'Political Party Chairman',
            ],

            // National Chairman (Party)
            [
                'Okechukwu Madu', 'Abia', 'Aba North', 'APC',
                'okechukwu.madu@example.com', '08089012345', 'National Chairman (Party)',
            ],
            [
                'Ifeanyi Uba', 'Anambra', 'Nnewi', 'PDP',
                'ifeanyi.uba@example.com', '08090123456', 'National Chairman (Party)',
            ],

            // Secretary to the Government
            [
                'Boss Mustapha', 'Adamawa', 'Yola', 'APC',
                'boss.mustapha@example.com', '08001234567', 'Secretary to the Government',
            ],
            [
                'Oladapo Afolabi', 'Ogun', 'Abeokuta', 'PDP',
                'oladapo.afolabi@example.com', '08012345678', 'Secretary to the Government',
            ],
        ];

        foreach ($hardcodedRepresentatives as $representative) {
            $this->insertRepresentativeData($representative);
        }
    }

    // Method to insert representative data
    private function insertRepresentativeData($data)
    {
        $name = trim($data[0] ?? '');
        $state = trim($data[1] ?? '');
        $district = trim($data[2] ?? '');
        $party = trim($data[3] ?? '');
        $email = trim($data[4] ?? '');
        $phone_number = trim($data[5] ?? '');
        $position = trim($data[6] ?? '');

        $phone_number = ($phone_number === 'N/A' || empty($phone_number)) ? null : $phone_number;

        if (empty($name)) {
            return;
        }

        if (empty($email)) {
            $email = Str::random(10) . '@example.com';
        } elseif (DB::table('accounts')->where('email', $email)->exists()) {
            return;
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
            Log::error('Failed to insert representative: ' . $e->getMessage(), ['row' => $data]);
        }
    }
}
