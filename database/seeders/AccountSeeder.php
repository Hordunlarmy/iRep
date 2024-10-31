<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    public function run()
    {
        $accountsData = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone_number' => '08012345678',
                'dob' => '1990-05-10',
                'state_id' => 27,
                'local_government_id' => 647,
                'location' => '12, Lagos Street, Abeokuta',
                'email_verified' => true,
                'password' => bcrypt('password456'),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone_number' => '08098765432',
                'dob' => '1988-07-15',
                'state_id' => 24,
                'local_government_id' => 597,
                'location' => '45, Ikorodu Road, Lagos',
                'email_verified' => false,
                'password' => bcrypt('password456'),
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael.johnson@example.com',
                'phone_number' => '08011223344',
                'dob' => '1992-09-23',
                'state_id' => 30,
                'local_government_id' => 740,
                'location' => '23, Ring Road, Ibadan',
                'email_verified' => true,
                'password' => bcrypt('password456'),
            ],
        ];

        DB::table('accounts')->insert($accountsData);

    }
}
