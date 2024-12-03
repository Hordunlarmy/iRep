<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = [
            // Abia State
            ['name' => 'Abia North', 'state_id' => 1],
            ['name' => 'Abia Central', 'state_id' => 1],
            ['name' => 'Abia South', 'state_id' => 1],

            // Adamawa State
            ['name' => 'Adamawa North', 'state_id' => 2],
            ['name' => 'Adamawa Central', 'state_id' => 2],
            ['name' => 'Adamawa South', 'state_id' => 2],

            // Akwa Ibom State
            ['name' => 'Akwa Ibom North-East', 'state_id' => 3],
            ['name' => 'Akwa Ibom North-West', 'state_id' => 3],
            ['name' => 'Akwa Ibom South-East', 'state_id' => 3],
            ['name' => 'Akwa Ibom South-West', 'state_id' => 3],

            // Anambra State
            ['name' => 'Anambra North', 'state_id' => 4],
            ['name' => 'Anambra Central', 'state_id' => 4],
            ['name' => 'Anambra South', 'state_id' => 4],

            // Bauchi State
            ['name' => 'Bauchi North', 'state_id' => 5],
            ['name' => 'Bauchi South', 'state_id' => 5],
            ['name' => 'Bauchi Central', 'state_id' => 5],

            // Bayelsa State
            ['name' => 'Bayelsa East', 'state_id' => 6],
            ['name' => 'Bayelsa West', 'state_id' => 6],
            ['name' => 'Bayelsa Central', 'state_id' => 6],

            // Benue State
            ['name' => 'Benue North-East', 'state_id' => 7],
            ['name' => 'Benue North-West', 'state_id' => 7],
            ['name' => 'Benue South', 'state_id' => 7],
            ['name' => 'Benue Central', 'state_id' => 7],

            // Borno State
            ['name' => 'Borno North', 'state_id' => 8],
            ['name' => 'Borno Central', 'state_id' => 8],
            ['name' => 'Borno South', 'state_id' => 8],

            // Cross River State
            ['name' => 'Cross River North', 'state_id' => 9],
            ['name' => 'Cross River Central', 'state_id' => 9],
            ['name' => 'Cross River South', 'state_id' => 9],

            // Delta State
            ['name' => 'Delta North', 'state_id' => 10],
            ['name' => 'Delta Central', 'state_id' => 10],
            ['name' => 'Delta South', 'state_id' => 10],

            // Ebonyi State
            ['name' => 'Ebonyi North', 'state_id' => 11],
            ['name' => 'Ebonyi Central', 'state_id' => 11],
            ['name' => 'Ebonyi South', 'state_id' => 11],

            // Edo State
            ['name' => 'Edo North', 'state_id' => 12],
            ['name' => 'Edo Central', 'state_id' => 12],
            ['name' => 'Edo South', 'state_id' => 12],

            // Ekiti State
            ['name' => 'Ekiti North', 'state_id' => 13],
            ['name' => 'Ekiti Central', 'state_id' => 13],
            ['name' => 'Ekiti South', 'state_id' => 13],

            // Enugu State
            ['name' => 'Enugu North', 'state_id' => 14],
            ['name' => 'Enugu East', 'state_id' => 14],
            ['name' => 'Enugu West', 'state_id' => 14],

            // Gombe State
            ['name' => 'Gombe North', 'state_id' => 15],
            ['name' => 'Gombe Central', 'state_id' => 15],
            ['name' => 'Gombe South', 'state_id' => 15],

            // Imo State
            ['name' => 'Imo North', 'state_id' => 16],
            ['name' => 'Imo Central', 'state_id' => 16],
            ['name' => 'Imo South', 'state_id' => 16],

            // Jigawa State
            ['name' => 'Jigawa North-East', 'state_id' => 17],
            ['name' => 'Jigawa North-West', 'state_id' => 17],
            ['name' => 'Jigawa Central', 'state_id' => 17],

            // Kaduna State
            ['name' => 'Kaduna North', 'state_id' => 18],
            ['name' => 'Kaduna Central', 'state_id' => 18],
            ['name' => 'Kaduna South', 'state_id' => 18],

            // Kano State
            ['name' => 'Kano North', 'state_id' => 19],
            ['name' => 'Kano Central', 'state_id' => 19],
            ['name' => 'Kano South', 'state_id' => 19],
            ['name' => 'Kano East', 'state_id' => 19],

            // Katsina State
            ['name' => 'Katsina North', 'state_id' => 20],
            ['name' => 'Katsina Central', 'state_id' => 20],
            ['name' => 'Katsina South', 'state_id' => 20],
            ['name' => 'Katsina East', 'state_id' => 20],

            // Kebbi State
            ['name' => 'Kebbi North', 'state_id' => 21],
            ['name' => 'Kebbi Central', 'state_id' => 21],
            ['name' => 'Kebbi South', 'state_id' => 21],

            // Kogi State
            ['name' => 'Kogi East', 'state_id' => 22],
            ['name' => 'Kogi Central', 'state_id' => 22],
            ['name' => 'Kogi West', 'state_id' => 22],

            // Kwara State
            ['name' => 'Kwara North', 'state_id' => 23],
            ['name' => 'Kwara Central', 'state_id' => 23],
            ['name' => 'Kwara South', 'state_id' => 23],

            // Lagos State
            ['name' => 'Lagos East', 'state_id' => 24],
            ['name' => 'Lagos West', 'state_id' => 24],
            ['name' => 'Lagos Central', 'state_id' => 24],

            // Nasarawa State
            ['name' => 'Nasarawa North', 'state_id' => 25],
            ['name' => 'Nasarawa Central', 'state_id' => 25],
            ['name' => 'Nasarawa South', 'state_id' => 25],
            ['name' => 'Nasarawa West', 'state_id' => 25],

            // Niger State
            ['name' => 'Niger East', 'state_id' => 26],
            ['name' => 'Niger Central', 'state_id' => 26],
            ['name' => 'Niger West', 'state_id' => 26],

            // Ogun State
            ['name' => 'Ogun East', 'state_id' => 27],
            ['name' => 'Ogun Central', 'state_id' => 27],
            ['name' => 'Ogun West', 'state_id' => 27],

            // Ondo State
            ['name' => 'Ondo North', 'state_id' => 28],
            ['name' => 'Ondo Central', 'state_id' => 28],
            ['name' => 'Ondo South', 'state_id' => 28],

            // Osun State
            ['name' => 'Osun East', 'state_id' => 29],
            ['name' => 'Osun Central', 'state_id' => 29],
            ['name' => 'Osun West', 'state_id' => 29],

            // Oyo State
            ['name' => 'Oyo North', 'state_id' => 30],
            ['name' => 'Oyo Central', 'state_id' => 30],
            ['name' => 'Oyo South', 'state_id' => 30],

            // Plateau State
            ['name' => 'Plateau North', 'state_id' => 31],
            ['name' => 'Plateau Central', 'state_id' => 31],
            ['name' => 'Plateau South', 'state_id' => 31],

            // Rivers State
            ['name' => 'Rivers East', 'state_id' => 32],
            ['name' => 'Rivers Central', 'state_id' => 32],
            ['name' => 'Rivers West', 'state_id' => 32],

            // Sokoto State
            ['name' => 'Sokoto East', 'state_id' => 33],
            ['name' => 'Sokoto Central', 'state_id' => 33],
            ['name' => 'Sokoto West', 'state_id' => 33],

            // Taraba State
            ['name' => 'Taraba North', 'state_id' => 34],
            ['name' => 'Taraba Central', 'state_id' => 34],
            ['name' => 'Taraba South', 'state_id' => 34],

            // Yobe State
            ['name' => 'Yobe North', 'state_id' => 35],
            ['name' => 'Yobe Central', 'state_id' => 35],
            ['name' => 'Yobe South', 'state_id' => 35],

            // Zamfara State
            ['name' => 'Zamfara North', 'state_id' => 36],
            ['name' => 'Zamfara Central', 'state_id' => 36],
            ['name' => 'Zamfara South', 'state_id' => 36],

            // Federal Capital Territory
            ['name' => 'Abuja', 'state_id' => 37],
        ];

        foreach ($districts as $district) {
            try {
                DB::table('districts')->insert($district);
            } catch (\Exception $e) {
                Log::error('Failed to insert district: ' . $e->getMessage(), $district);
            }
        }

    }
}
