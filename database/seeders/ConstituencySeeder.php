<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConstituencySeeder extends Seeder
{
    public function run()
    {
        $constituencies = [
            // Abia State
            ['name' => 'Abia North', 'state_id' => 1],
            ['name' => 'Abia Central', 'state_id' => 1],
            ['name' => 'Abia South', 'state_id' => 1],
            ['name' => 'Arochukwu/Ohafia', 'state_id' => 1],
            ['name' => 'Ikwuano/Umuahia', 'state_id' => 1],
            ['name' => 'Isiala Ngwa North', 'state_id' => 1],

            // Adamawa State
            ['name' => 'Adamawa Central', 'state_id' => 2],
            ['name' => 'Adamawa North', 'state_id' => 2],
            ['name' => 'Adamawa South', 'state_id' => 2],
            ['name' => 'Mubi North', 'state_id' => 2],
            ['name' => 'Mubi South', 'state_id' => 2],
            ['name' => 'Numan', 'state_id' => 2],

            // Akwa Ibom State
            ['name' => 'Akwa Ibom North-East', 'state_id' => 3],
            ['name' => 'Akwa Ibom North-West', 'state_id' => 3],
            ['name' => 'Akwa Ibom South-East', 'state_id' => 3],
            ['name' => 'Akwa Ibom South-West', 'state_id' => 3],
            ['name' => 'Eket', 'state_id' => 3],
            ['name' => 'Oron', 'state_id' => 3],
            ['name' => 'Uyo', 'state_id' => 3],
            ['name' => 'Ikono', 'state_id' => 3],
            ['name' => 'Ikot Ekpene', 'state_id' => 3],
            ['name' => 'Essien Udim', 'state_id' => 3],

            // Anambra State
            ['name' => 'Anambra North', 'state_id' => 4],
            ['name' => 'Anambra Central', 'state_id' => 4],
            ['name' => 'Anambra South', 'state_id' => 4],
            ['name' => 'Awka North', 'state_id' => 4],
            ['name' => 'Awka South', 'state_id' => 4],
            ['name' => 'Nnewi North', 'state_id' => 4],
            ['name' => 'Nnewi South', 'state_id' => 4],

            // Bauchi State
            ['name' => 'Bauchi North', 'state_id' => 5],
            ['name' => 'Bauchi South', 'state_id' => 5],
            ['name' => 'Bauchi Central', 'state_id' => 5],
            ['name' => 'Alkaleri', 'state_id' => 5],
            ['name' => 'Bogoro', 'state_id' => 5],
            ['name' => 'Darazo', 'state_id' => 5],

            // Bayelsa State
            ['name' => 'Bayelsa East', 'state_id' => 6],
            ['name' => 'Bayelsa West', 'state_id' => 6],
            ['name' => 'Bayelsa Central', 'state_id' => 6],
            ['name' => 'Ogbia', 'state_id' => 6],
            ['name' => 'Nembe', 'state_id' => 6],

            // Benue State
            ['name' => 'Benue North-East', 'state_id' => 7],
            ['name' => 'Benue North-West', 'state_id' => 7],
            ['name' => 'Benue South', 'state_id' => 7],
            ['name' => 'Guma', 'state_id' => 7],
            ['name' => 'Makurdi', 'state_id' => 7],
            ['name' => 'Gboko', 'state_id' => 7],
            ['name' => 'Otukpo', 'state_id' => 7],
            ['name' => 'Vandeikya', 'state_id' => 7],
            ['name' => 'Tarka', 'state_id' => 7],
            ['name' => 'Buruku', 'state_id' => 7],
            ['name' => 'Katsina-Ala', 'state_id' => 7],

            // Borno State
            ['name' => 'Borno North', 'state_id' => 8],
            ['name' => 'Borno Central', 'state_id' => 8],
            ['name' => 'Borno South', 'state_id' => 8],
            ['name' => 'Kaga', 'state_id' => 8],
            ['name' => 'Maiduguri', 'state_id' => 8],
            ['name' => 'Bayo', 'state_id' => 8],

            // Cross River State
            ['name' => 'Cross River North', 'state_id' => 9],
            ['name' => 'Cross River Central', 'state_id' => 9],
            ['name' => 'Cross River South', 'state_id' => 9],
            ['name' => 'Calabar South', 'state_id' => 9],
            ['name' => 'Calabar Municipality', 'state_id' => 9],
            ['name' => 'Biase', 'state_id' => 9],
            ['name' => 'Ikom', 'state_id' => 9],
            ['name' => 'Ogoja', 'state_id' => 9],
            ['name' => 'Obubra', 'state_id' => 9],
            ['name' => 'Etung', 'state_id' => 9],
            ['name' => 'Yakurr', 'state_id' => 9],

            // Delta State
            ['name' => 'Delta North', 'state_id' => 10],
            ['name' => 'Delta Central', 'state_id' => 10],
            ['name' => 'Delta South', 'state_id' => 10],
            ['name' => 'Ukwuani', 'state_id' => 10],
            ['name' => 'Ika North-East', 'state_id' => 10],
            ['name' => 'Ika South', 'state_id' => 10],
            ['name' => 'Aniocha North', 'state_id' => 10],
            ['name' => 'Aniocha South', 'state_id' => 10],
            ['name' => 'Ndokwa East', 'state_id' => 10],
            ['name' => 'Ndokwa West', 'state_id' => 10],
            ['name' => 'Isoko North', 'state_id' => 10],
            ['name' => 'Isoko South', 'state_id' => 10],
            ['name' => 'Bomadi', 'state_id' => 10],
            ['name' => 'Burutu', 'state_id' => 10],
            ['name' => 'Patani', 'state_id' => 10],
            ['name' => 'Warri North', 'state_id' => 10],
            ['name' => 'Warri South', 'state_id' => 10],
            ['name' => 'Warri South-West', 'state_id' => 10],

            // Ebonyi State
            ['name' => 'Ebonyi North', 'state_id' => 11],
            ['name' => 'Ebonyi Central', 'state_id' => 11],
            ['name' => 'Ebonyi South', 'state_id' => 11],
            ['name' => 'Abakaliki', 'state_id' => 11],
            ['name' => 'Ishielu', 'state_id' => 11],
            ['name' => 'Ohaozara', 'state_id' => 11],

            // Edo State
            ['name' => 'Edo North', 'state_id' => 12],
            ['name' => 'Edo Central', 'state_id' => 12],
            ['name' => 'Edo South', 'state_id' => 12],
            ['name' => 'Akoko-Edo', 'state_id' => 12],
            ['name' => 'Oredo', 'state_id' => 12],
            ['name' => 'Esan North-East', 'state_id' => 12],
            ['name' => 'Esan South-East', 'state_id' => 12],
            ['name' => 'Esan West', 'state_id' => 12],
            ['name' => 'Orhionmwon', 'state_id' => 12],
            ['name' => 'Igueben', 'state_id' => 12],

            // Ekiti State
            ['name' => 'Ekiti North', 'state_id' => 13],
            ['name' => 'Ekiti South', 'state_id' => 13],
            ['name' => 'Ekiti Central', 'state_id' => 13],
            ['name' => 'Ijero', 'state_id' => 13],
            ['name' => 'Irepodun-Ifelodun', 'state_id' => 13],
            ['name' => 'Emure-Ile', 'state_id' => 13],

            // Enugu State
            ['name' => 'Enugu North', 'state_id' => 14],
            ['name' => 'Enugu East', 'state_id' => 14],
            ['name' => 'Enugu West', 'state_id' => 14],
            ['name' => 'Igbo-Etiti', 'state_id' => 14],
            ['name' => 'Udenu', 'state_id' => 14],
            ['name' => 'Nsukka', 'state_id' => 14],
            ['name' => 'Udi', 'state_id' => 14],
            ['name' => 'Oji-River', 'state_id' => 14],

            // Gombe State
            ['name' => 'Gombe North', 'state_id' => 15],
            ['name' => 'Gombe Central', 'state_id' => 15],
            ['name' => 'Gombe South', 'state_id' => 15],
            ['name' => 'Akko', 'state_id' => 15],
            ['name' => 'Bauchi', 'state_id' => 15],
            ['name' => 'Dukku', 'state_id' => 15],

            // Imo State
            ['name' => 'Imo North', 'state_id' => 16],
            ['name' => 'Imo Central', 'state_id' => 16],
            ['name' => 'Imo South', 'state_id' => 16],
            ['name' => 'Oguta', 'state_id' => 16],
            ['name' => 'Owerri West', 'state_id' => 16],
            ['name' => 'Owerri North', 'state_id' => 16],
            ['name' => 'Mbaitoli', 'state_id' => 16],
            ['name' => 'Ikeduru', 'state_id' => 16],
            ['name' => 'Isu', 'state_id' => 16],

            // Jigawa State
            ['name' => 'Jigawa North-East', 'state_id' => 17],
            ['name' => 'Jigawa North-West', 'state_id' => 17],
            ['name' => 'Jigawa Central', 'state_id' => 17],
            ['name' => 'Gumel', 'state_id' => 17],
            ['name' => 'Hadejia', 'state_id' => 17],
            ['name' => 'Kazaure', 'state_id' => 17],
            ['name' => 'Miga', 'state_id' => 17],
            ['name' => 'Ringim', 'state_id' => 17],

            // Kaduna State
            ['name' => 'Kaduna North', 'state_id' => 18],
            ['name' => 'Kaduna Central', 'state_id' => 18],
            ['name' => 'Kaduna South', 'state_id' => 18],
            ['name' => 'Chikun', 'state_id' => 18],
            ['name' => 'Kachia', 'state_id' => 18],
            ['name' => 'Kajuru', 'state_id' => 18],
            ['name' => 'Sanga', 'state_id' => 18],
            ['name' => 'Jema’a', 'state_id' => 18],
            ['name' => 'Zangon Kataf', 'state_id' => 18],

            // Kano State
            ['name' => 'Kano North', 'state_id' => 19],
            ['name' => 'Kano Central', 'state_id' => 19],
            ['name' => 'Kano South', 'state_id' => 19],
            ['name' => 'Dawakin Tofa', 'state_id' => 19],
            ['name' => 'Gaya', 'state_id' => 19],
            ['name' => 'Kano Municipal', 'state_id' => 19],
            ['name' => 'Karaye', 'state_id' => 19],
            ['name' => 'Kibiya', 'state_id' => 19],
            ['name' => 'Madobi', 'state_id' => 19],
            ['name' => 'Nasarawa', 'state_id' => 19],
            ['name' => 'Rogo', 'state_id' => 19],
            ['name' => 'Tudun Wada', 'state_id' => 19],
            ['name' => 'Wudil', 'state_id' => 19],

            // Katsina State
            ['name' => 'Katsina North', 'state_id' => 20],
            ['name' => 'Katsina Central', 'state_id' => 20],
            ['name' => 'Katsina South', 'state_id' => 20],
            ['name' => 'Batsari', 'state_id' => 20],
            ['name' => 'Baure', 'state_id' => 20],
            ['name' => 'Dandume', 'state_id' => 20],
            ['name' => 'Dutsi', 'state_id' => 20],
            ['name' => 'Funtua', 'state_id' => 20],
            ['name' => 'Ingawa', 'state_id' => 20],
            ['name' => 'Kankara', 'state_id' => 20],
            ['name' => 'Katsina', 'state_id' => 20],
            ['name' => 'Kurfi', 'state_id' => 20],
            ['name' => 'Malumfashi', 'state_id' => 20],
            ['name' => 'Mashi', 'state_id' => 20],

            // Kebbi State
            ['name' => 'Kebbi North', 'state_id' => 21],
            ['name' => 'Kebbi Central', 'state_id' => 21],
            ['name' => 'Kebbi South', 'state_id' => 21],
            ['name' => 'Birnin Kebbi', 'state_id' => 21],
            ['name' => 'Zuru', 'state_id' => 21],
            ['name' => 'Sakaba', 'state_id' => 21],

            // Kogi State
            ['name' => 'Kogi Central', 'state_id' => 22],
            ['name' => 'Kogi East', 'state_id' => 22],
            ['name' => 'Kogi West', 'state_id' => 22],
            ['name' => 'Adavi', 'state_id' => 22],
            ['name' => 'Ajaokuta', 'state_id' => 22],
            ['name' => 'Bassa', 'state_id' => 22],
            ['name' => 'Dekina', 'state_id' => 22],
            ['name' => 'Igalamela-Odolu', 'state_id' => 22],
            ['name' => 'Idah', 'state_id' => 22],

            // Kwara State
            ['name' => 'Kwara North', 'state_id' => 23],
            ['name' => 'Kwara Central', 'state_id' => 23],
            ['name' => 'Kwara South', 'state_id' => 23],
            ['name' => 'Ilorin East', 'state_id' => 23],
            ['name' => 'Ilorin West', 'state_id' => 23],
            ['name' => 'Asa', 'state_id' => 23],

            // Lagos State
            ['name' => 'Lagos Central', 'state_id' => 24],
            ['name' => 'Lagos East', 'state_id' => 24],
            ['name' => 'Lagos West', 'state_id' => 24],
            ['name' => 'Agege', 'state_id' => 24],
            ['name' => 'Alimosho', 'state_id' => 24],
            ['name' => 'Apapa', 'state_id' => 24],
            ['name' => 'Badagry', 'state_id' => 24],
            ['name' => 'Lagos Mainland', 'state_id' => 24],
            ['name' => 'Ojo', 'state_id' => 24],
            ['name' => 'Ikorodu', 'state_id' => 24],
            ['name' => 'Shomolu', 'state_id' => 24],
            ['name' => 'Surulere', 'state_id' => 24],
            ['name' => 'Eti Osa', 'state_id' => 24],
            ['name' => 'Ibeju Lekki', 'state_id' => 24],
            ['name' => 'Mushin', 'state_id' => 24],
            ['name' => 'Victoria Island', 'state_id' => 24],
            ['name' => 'Lekki', 'state_id' => 24],

            // Nasarawa State
            ['name' => 'Nasarawa North', 'state_id' => 25],
            ['name' => 'Nasarawa South', 'state_id' => 25],
            ['name' => 'Nasarawa West', 'state_id' => 25],
            ['name' => 'Doma', 'state_id' => 25],
            ['name' => 'Keffi', 'state_id' => 25],
            ['name' => 'Akwanga', 'state_id' => 25],

            // Niger State
            ['name' => 'Niger North', 'state_id' => 26],
            ['name' => 'Niger Central', 'state_id' => 26],
            ['name' => 'Niger South', 'state_id' => 26],
            ['name' => 'Bida', 'state_id' => 26],
            ['name' => 'Minna', 'state_id' => 26],
            ['name' => 'Katcha', 'state_id' => 26],

            // Ogun State
            ['name' => 'Ogun Central', 'state_id' => 27],
            ['name' => 'Ogun East', 'state_id' => 27],
            ['name' => 'Ogun West', 'state_id' => 27],
            ['name' => 'Abeokuta North', 'state_id' => 27],
            ['name' => 'Abeokuta South', 'state_id' => 27],
            ['name' => 'Ijebu North', 'state_id' => 27],
            ['name' => 'Ijebu South', 'state_id' => 27],
            ['name' => 'Ogun Waterside', 'state_id' => 27],
            ['name' => 'Remo North', 'state_id' => 27],
            ['name' => 'Sagamu', 'state_id' => 27],

            // Ondo State
            ['name' => 'Ondo North', 'state_id' => 28],
            ['name' => 'Ondo Central', 'state_id' => 28],
            ['name' => 'Ondo South', 'state_id' => 28],
            ['name' => 'Akoko North-East', 'state_id' => 28],
            ['name' => 'Akoko North-West', 'state_id' => 28],
            ['name' => 'Akoko South-East', 'state_id' => 28],
            ['name' => 'Akoko South-West', 'state_id' => 28],
            ['name' => 'Ose', 'state_id' => 28],

            // Osun State
            ['name' => 'Osun East', 'state_id' => 29],
            ['name' => 'Osun Central', 'state_id' => 29],
            ['name' => 'Osun West', 'state_id' => 29],
            ['name' => 'Ife North', 'state_id' => 29],
            ['name' => 'Ife South', 'state_id' => 29],
            ['name' => 'Ilesha East', 'state_id' => 29],
            ['name' => 'Ilesha West', 'state_id' => 29],
            ['name' => 'Odo-Otin', 'state_id' => 29],

            // Oyo State
            ['name' => 'Oyo North', 'state_id' => 30],
            ['name' => 'Oyo Central', 'state_id' => 30],
            ['name' => 'Oyo South', 'state_id' => 30],
            ['name' => 'Akinyele', 'state_id' => 30],
            ['name' => 'Ogbomosho North', 'state_id' => 30],
            ['name' => 'Ogbomosho South', 'state_id' => 30],
            ['name' => 'Ibarapa East', 'state_id' => 30],
            ['name' => 'Ibarapa Central', 'state_id' => 30],
            ['name' => 'Ibarapa North', 'state_id' => 30],
            ['name' => 'Atiba', 'state_id' => 30],
            ['name' => 'Saki East', 'state_id' => 30],
            ['name' => 'Saki West', 'state_id' => 30],

            // Plateau State
            ['name' => 'Plateau North', 'state_id' => 31],
            ['name' => 'Plateau Central', 'state_id' => 31],
            ['name' => 'Plateau South', 'state_id' => 31],
            ['name' => 'Bokkos', 'state_id' => 31],
            ['name' => 'Jos North', 'state_id' => 31],
            ['name' => 'Jos South', 'state_id' => 31],
            ['name' => 'Langtang North', 'state_id' => 31],
            ['name' => 'Langtang South', 'state_id' => 31],

            // Rivers State
            ['name' => 'Rivers East', 'state_id' => 32],
            ['name' => 'Rivers West', 'state_id' => 32],
            ['name' => 'Rivers South-East', 'state_id' => 32],
            ['name' => 'Port Harcourt', 'state_id' => 32],
            ['name' => 'Obio/Akpor', 'state_id' => 32],
            ['name' => 'Okrika', 'state_id' => 32],

            // Sokoto State
            ['name' => 'Sokoto North', 'state_id' => 33],
            ['name' => 'Sokoto East', 'state_id' => 33],
            ['name' => 'Sokoto South', 'state_id' => 33],
            ['name' => 'Bodinga', 'state_id' => 33],
            ['name' => 'Dange-Shuni', 'state_id' => 33],
            ['name' => 'Gudu', 'state_id' => 33],
            ['name' => 'Wamakko', 'state_id' => 33],
            ['name' => 'Tambuwal', 'state_id' => 33],

            // Taraba State
            ['name' => 'Taraba North', 'state_id' => 34],
            ['name' => 'Taraba Central', 'state_id' => 34],
            ['name' => 'Taraba South', 'state_id' => 34],
            ['name' => 'Donga', 'state_id' => 34],
            ['name' => 'Gashaka', 'state_id' => 34],

            // Yobe State
            ['name' => 'Yobe North', 'state_id' => 35],
            ['name' => 'Yobe Central', 'state_id' => 35],
            ['name' => 'Yobe South', 'state_id' => 35],
            ['name' => 'Damaturu', 'state_id' => 35],
            ['name' => 'Bade', 'state_id' => 35],
            ['name' => 'Fika', 'state_id' => 35],

            // Zamfara State
            ['name' => 'Zamfara Central', 'state_id' => 36],
            ['name' => 'Zamfara North', 'state_id' => 36],
            ['name' => 'Zamfara South', 'state_id' => 36],
            ['name' => 'Anka', 'state_id' => 36],
            ['name' => 'Maradun', 'state_id' => 36],
            ['name' => 'Gummi', 'state_id' => 36],
            ['name' => 'Kaura-Namoda', 'state_id' => 36],

            // Federal Capital Territory
            ['name' => 'Abaji', 'state_id' => 37],
            ['name' => 'Bwari', 'state_id' => 37],
            ['name' => 'Gwagwalada', 'state_id' => 37],
            ['name' => 'Kuje', 'state_id' => 37],
            ['name' => 'Kwali', 'state_id' => 37],
        ];

        DB::table('constituencies')->insert($constituencies);
    }
}
