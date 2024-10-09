<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitizenSeeder extends Seeder
{
    public function run()
    {
        $citizensData = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone_number' => '08012345678',
                'dob' => '1990-05-10',
                'state' => 'Ogun',
                'local_government' => 'Abeokuta South',
                'location' => '12, Lagos Street, Abeokuta',
                'occupation' => 'Engineer',
                'email_verified' => true,
                'password' => bcrypt('password456'),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone_number' => '08098765432',
                'dob' => '1988-07-15',
                'state' => 'Lagos',
                'local_government' => 'Ikeja',
                'location' => '45, Ikorodu Road, Lagos',
                'occupation' => 'Doctor',
                'email_verified' => false,
                'password' => bcrypt('password456'),
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael.johnson@example.com',
                'phone_number' => '08011223344',
                'dob' => '1992-09-23',
                'state' => 'Oyo',
                'local_government' => 'Ibadan North',
                'location' => '23, Ring Road, Ibadan',
                'occupation' => 'Teacher',
                'email_verified' => true,
                'password' => bcrypt('password456'),
            ],
        ];

        foreach ($citizensData as $citizen) {
            $accountId = DB::table('accounts')->insertGetId([
                'name' => $citizen['name'],
                'email' => $citizen['email'],
                'phone_number' => $citizen['phone_number'],
                'dob' => $citizen['dob'],
                'state' => $citizen['state'],
                'local_government' => $citizen['local_government'],
                'password' => $citizen['password'],
                'account_type' => 1,
                'email_verified' => $citizen['email_verified'],
            ]);

            DB::table('citizens')->insert([
                'account_id' => $accountId,
                'occupation' => $citizen['occupation'],
                'location' => $citizen['location'],
            ]);
        }
    }
}
