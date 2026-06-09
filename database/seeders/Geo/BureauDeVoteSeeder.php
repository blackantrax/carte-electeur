<?php

namespace Database\Seeders\Geo;

use App\Models\BureauDeVote;
use Illuminate\Database\Seeder;

class BureauDeVoteSeeder extends Seeder
{
    public function run()
    {
        $bureaux = [
            // Yaoundé - Elig-Essono
            [
                'code' => 'CE-MF-01-01-001', 
                'nom' => 'Lycée d\'Elig-Essono', 
                'adresse' => 'Carrefour Elig-Essono', 
                'commune_code' => 'CE-MF-01',
                'arrondissement_code' => 'CE-MF-01-01',
                'nombre_electeurs' => 500,
                'localisation' => '3.8667,11.5167'
            ],
            
            // Douala - Bonapriso
            [
                'code' => 'LT-WO-01-01-001', 
                'nom' => 'École Publique Bonapriso', 
                'adresse' => 'Rue de Bonapriso', 
                'commune_code' => 'LT-WO-01',
                'arrondissement_code' => 'LT-WO-01-01',
                'nombre_electeurs' => 450,
                'localisation' => '4.0500,9.7000'
            ],
            
            // Ajouter tous les autres bureaux de vote...
        ];

        foreach ($bureaux as $bureau) {
            BureauDeVote::updateOrCreate(['code' => $bureau['code']], $bureau);
        }
    }
}