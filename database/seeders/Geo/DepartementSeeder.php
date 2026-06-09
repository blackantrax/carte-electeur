<?php

namespace Database\Seeders\Geo;

use App\Models\Departement;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    public function run()
    {
        $departements = [
            // Adamaoua
            ['code' => 'AD-DJ', 'nom' => 'Djérem', 'chef_lieu' => 'Tibati', 'region_code' => 'AD', 'population' => 120000, 'superficie' => 13283],
            ['code' => 'AD-FA', 'nom' => 'Faro-et-Déo', 'chef_lieu' => 'Tignère', 'region_code' => 'AD', 'population' => 80000, 'superficie' => 10335],
            
            // Centre
            ['code' => 'CE-MF', 'nom' => 'Mfoundi', 'chef_lieu' => 'Yaoundé', 'region_code' => 'CE', 'population' => 1500000, 'superficie' => 297],
            
            // Littoral
            ['code' => 'LT-WO', 'nom' => 'Wouri', 'chef_lieu' => 'Douala', 'region_code' => 'LT', 'population' => 2000000, 'superficie' => 923],
            
            // Extrême-Nord
            ['code' => 'EN-DI', 'nom' => 'Diamaré', 'chef_lieu' => 'Maroua', 'region_code' => 'EN', 'population' => 1000000, 'superficie' => 4665],
            
            // Ajouter tous les autres départements...
        ];

        foreach ($departements as $departement) {
            Departement::updateOrCreate(['code' => $departement['code']], $departement);
        }
    }
}