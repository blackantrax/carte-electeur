<?php

namespace Database\Seeders\Geo;

use App\Models\Commune;
use Illuminate\Database\Seeder;

class CommuneSeeder extends Seeder
{
    public function run()
    {
        $communes = [
            // Yaoundé
            ['code' => 'CE-MF-01', 'nom' => 'Yaoundé I', 'type' => 'Urbaine', 'departement_code' => 'CE-MF', 'population' => 250000],
            ['code' => 'CE-MF-02', 'nom' => 'Yaoundé II', 'type' => 'Urbaine', 'departement_code' => 'CE-MF', 'population' => 300000],
            
            // Douala
            ['code' => 'LT-WO-01', 'nom' => 'Douala I', 'type' => 'Urbaine', 'departement_code' => 'LT-WO', 'population' => 350000],
            ['code' => 'LT-WO-02', 'nom' => 'Douala II', 'type' => 'Urbaine', 'departement_code' => 'LT-WO', 'population' => 400000],
            
            // Maroua
            ['code' => 'EN-DI-01', 'nom' => 'Maroua I', 'type' => 'Urbaine', 'departement_code' => 'EN-DI', 'population' => 150000],
            
            // Zones rurales
            ['code' => 'AD-DJ-01', 'nom' => 'Tibati', 'type' => 'Rurale', 'departement_code' => 'AD-DJ', 'population' => 50000],
            
            // Ajouter toutes les autres communes...
        ];

        foreach ($communes as $commune) {
            Commune::updateOrCreate(['code' => $commune['code']], $commune);
        }
    }
}