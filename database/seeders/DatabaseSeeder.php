<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            \Database\Seeders\Geo\PaysSeeder::class,
            \Database\Seeders\Geo\RegionSeeder::class,
            \Database\Seeders\Geo\DepartementSeeder::class,
            \Database\Seeders\Geo\CommuneSeeder::class,
            \Database\Seeders\Geo\ArrondissementSeeder::class,
            \Database\Seeders\Geo\BureauDeVoteSeeder::class,
            \Database\Seeders\Geo\AmbassadeSeeder::class,
        ]);
    }
}