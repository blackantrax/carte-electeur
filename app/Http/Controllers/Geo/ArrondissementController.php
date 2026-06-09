<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Models\Arrondissement;
use App\Models\Commune;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArrondissementController extends Controller
{
    public function index(Request $request)
    {
        // 1. Récupérer et normaliser les filtres de l'URL
        $filters = [
            'search' => $request->input('search', ''),
            'commune_code' => $request->input('commune_code', ''),
            'sort_field' => $request->input('sort_field', 'nom'),
            'sort_direction' => $request->input('sort_direction', 'asc'),
        ];

        // 2. Valider le champ de tri
        $allowedSorts = ['nom', 'code', 'chef_lieu'];
        if (!in_array($filters['sort_field'], $allowedSorts, true)) {
            $filters['sort_field'] = 'nom';
        }

        // 3. Construire la requête avec les relations + filtres
        $arrondissementsQuery = Arrondissement::with(['commune', 'commune.departement', 'commune.departement.region'])
            ->when($filters['search'], function ($q) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('nom', 'like', $term)
                      ->orWhere('code', 'like', $term)
                      ->orWhere('chef_lieu', 'like', $term);
                });
            })
            ->when($filters['commune_code'], fn ($q, $code) => $q->where('commune_code', $code))
            ->orderBy($filters['sort_field'], $filters['sort_direction']);

        // 4. Pagination avec conservation des paramètres
        $arrondissements = $arrondissementsQuery->paginate(12)->withQueryString();

        // 5. Envoyer les données à la vue
        return Inertia::render('Dashboard/Geo/Arrondissements/Index', [
            'arrondissements' => $arrondissements,
            'allCommunes' => Commune::with('departement.region')->get()->map(function ($commune) {
                return [
                    'code' => $commune->code,
                    'nom' => $commune->nom,
                    'departement_nom' => $commune->departement->nom ?? 'Non spécifié',
                    'region_nom' => $commune->departement->region->nom ?? 'Non spécifié'
                ];
            }),
            'filters' => $filters,
            'title' => 'Gestion des Arrondissements'
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/Geo/Arrondissements/Form', [
            'allCommunes' => Commune::with('departement.region')->get()->map(function ($commune) {
                return [
                    'code' => $commune->code,
                    'nom' => $commune->nom,
                    'departement_nom' => $commune->departement->nom ?? 'Non spécifié',
                    'region_nom' => $commune->departement->region->nom ?? 'Non spécifié'
                ];
            }),
            'title' => 'Créer un arrondissement'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:15|unique:arrondissements',
            'nom' => 'required|string|max:100',
            'commune_code' => 'required|exists:communes,code',
            'chef_lieu' => 'nullable|string|max:100'
        ]);

        Arrondissement::create($validated);

        return redirect()->route('admin.geo.arrondissements.index')
            ->with('success', 'Arrondissement créé avec succès');
    }

    public function edit(Arrondissement $arrondissement)
    {
        return Inertia::render('Dashboard/Geo/Arrondissements/Form', [
            'arrondissement' => $arrondissement,
            'allCommunes' => Commune::with('departement.region')->get()->map(function ($commune) {
                return [
                    'code' => $commune->code,
                    'nom' => $commune->nom,
                    'departement_nom' => $commune->departement->nom ?? 'Non spécifié',
                    'region_nom' => $commune->departement->region->nom ?? 'Non spécifié'
                ];
            }),
            'title' => 'Modifier l\'arrondissement'
        ]);
    }

    public function update(Request $request, Arrondissement $arrondissement)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:15|unique:arrondissements,code,'.$arrondissement->code.',code',
            'nom' => 'required|string|max:100',
            'commune_code' => 'required|exists:communes,code',
            'chef_lieu' => 'nullable|string|max:100'
        ]);

        $arrondissement->update($validated);

        return redirect()->route('admin.geo.arrondissements.index')
            ->with('success', 'Arrondissement mis à jour avec succès');
    }

    public function destroy(Arrondissement $arrondissement)
    {
        $arrondissement->delete();

        return redirect()->route('admin.geo.arrondissements.index')
            ->with('success', 'Arrondissement supprimé avec succès');
    }
}