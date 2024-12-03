<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(PartySeeder::class);
        $this->call(StateSeeder::class);
        $this->call(LocalGovernmentSeeder::class);
        $this->call(PositionSeeder::class);
        $this->call(DistrictSeeder::class);
        $this->call(ConstituencySeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(AccountSeeder::class);
        $this->call(RepresentativeSeeder::class);
        $this->call(PostSeeder::class);
        $this->call(AdminDataSeeder::class);
    }
}
