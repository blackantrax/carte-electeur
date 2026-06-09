<?php

namespace Database\Seeders\Geo;

use App\Models\Arrondissement;
use Illuminate\Database\Seeder;

class ArrondissementSeeder extends Seeder
{
    public function run()
    {
        $arrondissements = [
            // Yaoundé I
            ['code' => 'CE-MF-01-01', 'nom' => 'Elig-Essono', 'commune_code' => 'CE-MF-01', 'chef_lieu' => 'Elig-Essono'],
            ['code' => 'CE-MF-01-02', 'nom' => 'Efoulan', 'commune_code' => 'CE-MF-01', 'chef_lieu' => 'Efoulan'],
            
            // Douala I
            ['code' => 'LT-WO-01-01', 'nom' => 'Bonapriso', 'commune_code' => 'LT-WO-01', 'chef_lieu' => 'Bonapriso'],
            
            // Ajouter tous les autres arrondissements...
        ];

        foreach ($arrondissements as $arrondissement) {
            Arrondissement::updateOrCreate(['code' => $arrondissement['code']], $arrondissement);
        }
    }
}