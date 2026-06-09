<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Departement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommuneController extends Controller
{
    public function index(Request $request)
    {
        // 1. Récupérer et normaliser les filtres de l'URL
        $filters = [
            'search' => $request->input('search', ''),
            'departement_code' => $request->input('departement_code', ''),
            'type' => $request->input('type', ''),
            'sort_field' => $request->input('sort_field', 'nom'),
            'sort_direction' => $request->input('sort_direction', 'asc'),
        ];

        // 2. Valider le champ de tri pour éviter toute injection
        $allowedSorts = ['nom', 'code', 'type', 'population'];
        if (!in_array($filters['sort_field'], $allowedSorts, true)) {
            $filters['sort_field'] = 'nom';
        }

        // 3. Construire la requête avec les relations + filtres dynamiques
        $communesQuery = Commune::with(['departement', 'departement.region'])
            ->when($filters['search'], function ($q) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('nom', 'like', $term)
                      ->orWhere('code', 'like', $term)
                      ->orWhere('maire', 'like', $term);
                });
            })
            ->when($filters['departement_code'], fn ($q, $code) => $q->where('departement_code', $code))
            ->when($filters['type'], fn ($q, $type) => $q->where('type', $type))
            ->orderBy($filters['sort_field'], $filters['sort_direction']);

        // 4. Paginer tout en conservant la chaîne de requête
        $communes = $communesQuery->paginate(12)->withQueryString();

        // 5. Envoyer les données à la vue
        return Inertia::render('Dashboard/Geo/Communes/Index', [
            'communes' => $communes,
            'allDepartements' => Departement::with('region')->get()->map(function ($departement) {
                return [
                    'code' => $departement->code,
                    'nom' => $departement->nom,
                    'region_nom' => $departement->region->nom ?? 'Non spécifié'
                ];
            }),
            'types' => ['Urbaine', 'Rurale'],
            'filters' => $filters,
            'title' => 'Gestion des Communes'
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/Geo/Communes/Form', [
            'allDepartements' => Departement::with('region')->get()->map(function ($departement) {
                return [
                    'code' => $departement->code,
                    'nom' => $departement->nom,
                    'region_nom' => $departement->region->nom ?? 'Non spécifié'
                ];
            }),
            'types' => ['Urbaine', 'Rurale'],
            'title' => 'Créer une commune'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:8|unique:communes',
            'nom' => 'required|string|max:100',
            'type' => 'required|in:Urbaine,Rurale',
            'departement_code' => 'required|exists:departements,code',
            'maire' => 'nullable|string|max:100',
            'population' => 'nullable|integer|min:0'
        ]);

        Commune::create($validated);

        return redirect()->route('admin.geo.communes.index')
            ->with('success', 'Commune créée avec succès');
    }

    public function edit(Commune $commune)
    {
        return Inertia::render('Dashboard/Geo/Communes/Form', [
            'commune' => $commune,
            'allDepartements' => Departement::with('region')->get()->map(function ($departement) {
                return [
                    'code' => $departement->code,
                    'nom' => $departement->nom,
                    'region_nom' => $departement->region->nom ?? 'Non spécifié'
                ];
            }),
            'types' => ['Urbaine', 'Rurale'],
            'title' => 'Modifier la commune'
        ]);
    }

    public function update(Request $request, $commune)
    {
        $commune = Commune::where('code', $commune)->firstOrFail();

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'type' => 'required|in:Urbaine,Rurale',
            'departement_code' => 'required|exists:departements,code',
            'maire' => 'nullable|string|max:100',
            'population' => 'nullable|integer|min:0'
        ]);

        $commune->update($validated);

        return redirect()->route('admin.geo.communes.index')
            ->with('success', 'Commune mise à jour avec succès');
    }

    public function destroy(Commune $commune)
    {
        $commune->delete();

        return redirect()->route('admin.geo.communes.index')
            ->with('success', 'Commune supprimée avec succès');
    }
}