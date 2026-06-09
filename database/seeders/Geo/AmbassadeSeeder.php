<?php

namespace Database\Seeders\Geo;

use App\Models\Ambassade;
use Illuminate\Database\Seeder;

class AmbassadeSeeder extends Seeder
{
    public function run()
    {
        $ambassades = [
            [
                'code' => 'FR-01',
                'nom' => 'Ambassade du Cameroun en France',
                'type' => 'Ambassade',
                'pays_code' => 'FR',
                'ville' => 'Paris',
                'adresse' => '73 rue d\'Auteuil, 75016 Paris',
                'telephone' => '+33145620020',
                'nombre_electeurs' => 15000
            ],
            [
                'code' => 'US-01',
                'nom' => 'Ambassade du Cameroun aux USA',
                'type' => 'Ambassade',
                'pays_code' => 'US',
                'ville' => 'Washington',
                'adresse' => '2349 Massachusetts Avenue NW, Washington, DC 20008',
                'telephone' => '+12022657235',
                'nombre_electeurs' => 8000
            ],
            // Ajouter toutes les autres ambassades...
        ];

        foreach ($ambassades as $ambassade) {
            Ambassade::updateOrCreate(['code' => $ambassade['code']], $ambassade);
        }
    }
}