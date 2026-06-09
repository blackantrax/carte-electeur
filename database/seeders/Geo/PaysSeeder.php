<?php

namespace Database\Seeders\Geo;

use App\Models\Pays;
use Illuminate\Database\Seeder;

class PaysSeeder extends Seeder
{
    public function run()
    {
        $pays = [
            ['code' => 'CM', 'nom' => 'Cameroun', 'continent' => 'Afrique', 'indicatif_telephonique' => '+237'],
            ['code' => 'FR', 'nom' => 'France', 'continent' => 'Europe', 'indicatif_telephonique' => '+33'],
            ['code' => 'US', 'nom' => 'États-Unis', 'continent' => 'Amérique', 'indicatif_telephonique' => '+1'],
            ['code' => 'BE', 'nom' => 'Belgique', 'continent' => 'Europe', 'indicatif_telephonique' => '+32'],
            ['code' => 'DE', 'nom' => 'Allemagne', 'continent' => 'Europe', 'indicatif_telephonique' => '+49'],
            ['code' => 'GB', 'nom' => 'Royaume-Uni', 'continent' => 'Europe', 'indicatif_telephonique' => '+44'],
            ['code' => 'CN', 'nom' => 'Chine', 'continent' => 'Asie', 'indicatif_telephonique' => '+86'],
        ];

        foreach ($pays as $pay) {
            Pays::updateOrCreate(['code' => $pay['code']], $pay);
        }
    }
}