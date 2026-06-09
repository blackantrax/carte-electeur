<?php

namespace Database\Seeders\Geo;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run()
    {
        $regions = [
            ['code' => 'AD', 'nom' => 'Adamaoua', 'chef_lieu' => 'Ngaoundéré', 'population' => 1200000, 'superficie' => 63691],
            ['code' => 'CE', 'nom' => 'Centre', 'chef_lieu' => 'Yaoundé', 'population' => 4100000, 'superficie' => 68926],
            ['code' => 'EN', 'nom' => 'Extrême-Nord', 'chef_lieu' => 'Maroua', 'population' => 4000000, 'superficie' => 34246],
            ['code' => 'ES', 'nom' => 'Est', 'chef_lieu' => 'Bertoua', 'population' => 900000, 'superficie' => 109011],
            ['code' => 'LT', 'nom' => 'Littoral', 'chef_lieu' => 'Douala', 'population' => 3500000, 'superficie' => 20239],
            ['code' => 'NO', 'nom' => 'Nord', 'chef_lieu' => 'Garoua', 'population' => 2200000, 'superficie' => 65576],
            ['code' => 'NW', 'nom' => 'Nord-Ouest', 'chef_lieu' => 'Bamenda', 'population' => 2000000, 'superficie' => 17300],
            ['code' => 'OU', 'nom' => 'Ouest', 'chef_lieu' => 'Bafoussam', 'population' => 1900000, 'superficie' => 13872],
            ['code' => 'SU', 'nom' => 'Sud', 'chef_lieu' => 'Ebolowa', 'population' => 700000, 'superficie' => 47110],
            ['code' => 'SW', 'nom' => 'Sud-Ouest', 'chef_lieu' => 'Buéa', 'population' => 1500000, 'superficie' => 25410],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(['code' => $region['code']], $region);
        }
    }
}