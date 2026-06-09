<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Pays;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        // 1. Récupérer et normaliser les filtres de l’URL
        $filters = [
            'search'         => $request->input('search', ''),
            'pays'           => $request->input('pays', ''),
            'sort_field'     => $request->input('sort_field', 'nom'),
            'sort_direction' => $request->input('sort_direction', 'asc'),
        ];

        // 2. Valider le champ de tri pour éviter toute injection
        $allowedSorts = ['nom', 'code'];
        if (! in_array($filters['sort_field'], $allowedSorts, true)) {
            $filters['sort_field'] = 'nom';
        }

        // 3. Construire la requête avec les relations + filtres dynamiques
        $regionsQuery = Region::with('pays')
            ->when($filters['search'], function ($q) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $q->where(fn ($q) => $q
                    ->where('nom', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhere('chef_lieu', 'like', $term)
                );
            })
            ->when($filters['pays'], fn ($q) => $q->where('pays_code', $filters['pays']))
            ->orderBy($filters['sort_field'], $filters['sort_direction']);

        // 4. Paginer tout en conservant la chaîne de requête
        $regions = $regionsQuery->paginate(10)->withQueryString();

        // 5. Envoyer la liste des pays pour le sélecteur et les filtres actuels pour la vue
        return Inertia::render('Dashboard/Geo/Regions/Index', [
            'regions' => $regions,
            'allPays' => Pays::select('code', 'nom')->orderBy('nom')->get(),
            'filters' => $filters,
            'title'   => 'Régions',
        ]);
    }

    public function create()
    {
        $pays = Pays::select('code', 'nom')->get();

        return Inertia::render('Geo/Regions/Form', [
            'title' => 'Créer une région',
            'pays' => $pays,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:regions',
            'nom' => 'required|string|max:100',
            'chef_lieu' => 'required|string|max:100',
            'population' => 'nullable|integer',
            'superficie' => 'nullable|numeric',
            'pays_code' => 'required|string|exists:pays,code',
        ]);

        Region::create($validated);

        return redirect()->route('admin.geo.regions.index')
                         ->with('success', 'Région créée avec succès');
    }

    public function edit(Region $region)
    {
        $region->load('pays');
        $pays = Pays::select('code', 'nom')->get();

        return Inertia::render('Geo/Regions/Form', [
            'region' => $region,
            'pays' => $pays,
            'title' => 'Modifier la région'
        ]);
    }

    public function update(Request $request, $region)
    {
        $region = Region::where('code', $region)->firstOrFail();
        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:regions,code,' . $region->code . ',code',
            'nom' => 'required|string|max:100',
            'chef_lieu' => 'required|string|max:100',
            'population' => 'nullable|integer',
            'superficie' => 'nullable|numeric',
            'pays_code' => 'required|string|exists:pays,code',
        ]);

        $region->update($validated);

        return redirect()->route('admin.geo.regions.index')
                         ->with('success', 'Région mise à jour');
    }

    public function destroy(Region $region)
    {
        $region->delete();

        return redirect()->route('admin.geo.regions.index')
                         ->with('success', 'Région supprimée');
    }
}