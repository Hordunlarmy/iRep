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
        $this->call(CitizenSeeder::class);
        $this->call(RepresentativeSeeder::class);
        $this->call(PostSeeder::class);
    }
}
