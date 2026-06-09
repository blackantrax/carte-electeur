<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Models\Departement;
use App\Models\Region;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartementController extends Controller
{
    public function index(Request $request)
{
    // 1. Récupérer et normaliser les filtres de l'URL
    $filters = [
        'search' => $request->input('search', ''),
        'region_code' => $request->input('region_code', ''),
        'sort_field' => $request->input('sort_field', 'nom'),
        'sort_direction' => $request->input('sort_direction', 'asc'),
    ];

    // 2. Valider le champ de tri pour éviter toute injection
    $allowedSorts = ['nom', 'code', 'chef_lieu'];
    if (!in_array($filters['sort_field'], $allowedSorts, true)) {
        $filters['sort_field'] = 'nom';
    }

    // 3. Construire la requête avec les relations + filtres dynamiques
    $departementsQuery = Departement::with('region')
        ->when($filters['search'], function ($q) use ($filters) {
            $term = '%' . $filters['search'] . '%';
            $q->where(function ($q) use ($term) {
                $q->where('nom', 'like', $term)
                  ->orWhere('code', 'like', $term)
                  ->orWhere('chef_lieu', 'like', $term);
            });
        })
        ->when($filters['region_code'], fn ($q, $region_code) => 
            $q->where('region_code', $region_code))
        ->orderBy($filters['sort_field'], $filters['sort_direction']);

    // 4. Paginer tout en conservant la chaîne de requête
    $departements = $departementsQuery->paginate(12)->withQueryString();

    // 5. Envoyer les données à la vue
    return Inertia::render('Dashboard/Geo/Departements/Index', [
        'departements' => $departements,
        'allRegions' => Region::select('code', 'nom')->orderBy('nom')->get(),
        'filters' => $filters,
        'title' => 'Gestion des Départements'
    ]);
}

    public function create()
    {
        $regions = Region::all();
        
        return Inertia::render('Geo/Departements/Form', [
            'regions' => $regions,
            'title' => 'Créer un département'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:5|unique:departements',
            'nom' => 'required|string|max:100',
            'chef_lieu' => 'required|string|max:100',
            'region_code' => 'required|exists:regions,code',
            'population' => 'nullable|integer',
            'superficie' => 'nullable|numeric'
        ]);

        Departement::create($validated);

        return redirect()->route('admin.geo.departements.index')
                         ->with('success', 'Département créé avec succès');
    }

    public function edit(Departement $departement)
    {
        $regions = Region::all();
        
        return Inertia::render('Geo/Departements/Form', [
            'departement' => $departement,
            'regions' => $regions,
            'title' => 'Modifier le département'
        ]);
    }

    public function update(Request $request, $departement)
    {
        $departement = Departement::where('code', $departement)->firstOrFail();
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'chef_lieu' => 'required|string|max:100',
            'region_code' => 'required|exists:regions,code',
            'population' => 'nullable|integer',
            'superficie' => 'nullable|numeric'
        ]);

        $departement->update($validated);

        return redirect()->route('admin.geo.departements.index')
                         ->with('success', 'Département mis à jour');
    }

    public function destroy(Departement $departement)
    {
        $departement->delete();

        return redirect()->route('admin.geo.departements.index')
                         ->with('success', 'Département supprimé');
    }
}