<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Models\BureauDeVote;
use App\Models\Arrondissement;
use App\Models\Commune;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BureauDeVoteController extends Controller
{
    public function index(Request $request)
    {
        // 1. Récupérer et normaliser les filtres de l'URL
        $filters = [
            'search' => $request->input('search', ''),
            'commune_code' => $request->input('commune_code', ''),
            'arrondissement_code' => $request->input('arrondissement_code', ''),
            'sort_field' => $request->input('sort_field', 'nom'),
            'sort_direction' => $request->input('sort_direction', 'asc'),
        ];

        // 2. Valider le champ de tri pour éviter toute injection
        $allowedSorts = ['nom', 'code', 'nombre_electeurs'];
        if (!in_array($filters['sort_field'], $allowedSorts, true)) {
            $filters['sort_field'] = 'nom';
        }

        // 3. Construire la requête avec les relations + filtres dynamiques
        $bureauxQuery = BureauDeVote::with(['commune.departement.region', 'arrondissement'])
            ->when($filters['search'], function ($q) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('nom', 'like', $term)
                      ->orWhere('code', 'like', $term)
                      ->orWhere('adresse', 'like', $term);
                });
            })
            ->when($filters['commune_code'], fn ($q, $code) => $q->where('commune_code', $code))
            ->when($filters['arrondissement_code'], fn ($q, $code) => $q->where('arrondissement_code', $code))
            ->orderBy($filters['sort_field'], $filters['sort_direction']);

        // 4. Paginer tout en conservant la chaîne de requête
        $bureaux = $bureauxQuery->paginate(12)->withQueryString();

        // 5. Envoyer les données à la vue
        return Inertia::render('Dashboard/Geo/BureauxDeVote/Index', [
            'bureaux' => $bureaux,
            'allCommunes' => Commune::with('departement.region')->get()->map(function ($commune) {
                return [
                    'code' => $commune->code,
                    'nom' => $commune->nom,
                    'departement_nom' => $commune->departement->nom ?? 'Non spécifié',
                    'region_nom' => $commune->departement->region->nom ?? 'Non spécifié'
                ];
            }),
            'allArrondissements' => Arrondissement::with('commune')->get()->map(function ($arrondissement) {
                return [
                    'code' => $arrondissement->code,
                    'nom' => $arrondissement->nom,
                    'commune_nom' => $arrondissement->commune->nom ?? 'Non spécifié'
                ];
            }),
            'filters' => $filters,
            'title' => 'Gestion des Bureaux de Vote'
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/Geo/BureauxDeVote/Form', [
            'allCommunes' => Commune::with('departement.region')->get()->map(function ($commune) {
                return [
                    'code' => $commune->code,
                    'nom' => $commune->nom,
                    'departement_nom' => $commune->departement->nom ?? 'Non spécifié',
                    'region_nom' => $commune->departement->region->nom ?? 'Non spécifié'
                ];
            }),
            'allArrondissements' => Arrondissement::with('commune')->get()->map(function ($arrondissement) {
                return [
                    'code' => $arrondissement->code,
                    'nom' => $arrondissement->nom,
                    'commune_nom' => $arrondissement->commune->nom ?? 'Non spécifié'
                ];
            }),
            'title' => 'Créer un Bureau de Vote'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:bureau_de_votes',
            'nom' => 'required|string|max:200',
            'adresse' => 'required|string|max:255',
            'commune_code' => 'required|exists:communes,code',
            'arrondissement_code' => 'nullable|exists:arrondissements,code',
            'nombre_electeurs' => 'required|integer|min:0',
            'localisation' => 'nullable|string|max:100',
            'description' => 'nullable|string'
        ]);

        BureauDeVote::create($validated);

        return redirect()->route('admin.geo.bureaux-de-vote.index')
            ->with('success', 'Bureau de vote créé avec succès');
    }

    public function edit(BureauDeVote $bureauDeVote)
    {
        return Inertia::render('Dashboard/Geo/BureauxDeVote/Form', [
            'bureau' => $bureauDeVote,
            'allCommunes' => Commune::with('departement.region')->get()->map(function ($commune) {
                return [
                    'code' => $commune->code,
                    'nom' => $commune->nom,
                    'departement_nom' => $commune->departement->nom ?? 'Non spécifié',
                    'region_nom' => $commune->departement->region->nom ?? 'Non spécifié'
                ];
            }),
            'allArrondissements' => Arrondissement::with('commune')->get()->map(function ($arrondissement) {
                return [
                    'code' => $arrondissement->code,
                    'nom' => $arrondissement->nom,
                    'commune_nom' => $arrondissement->commune->nom ?? 'Non spécifié'
                ];
            }),
            'title' => 'Modifier le Bureau de Vote'
        ]);
    }

    public function update(Request $request, BureauDeVote $bureauDeVote)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:bureau_de_votes,code,'.$bureauDeVote->code.',code',
            'nom' => 'required|string|max:200',
            'adresse' => 'required|string|max:255',
            'commune_code' => 'required|exists:communes,code',
            'arrondissement_code' => 'nullable|exists:arrondissements,code',
            'nombre_electeurs' => 'required|integer|min:0',
            'localisation' => 'nullable|string|max:100',
            'description' => 'nullable|string'
        ]);

        $bureauDeVote->update($validated);

        return redirect()->route('admin.geo.bureaux-de-vote.index')
            ->with('success', 'Bureau de vote mis à jour avec succès');
    }

    public function destroy(BureauDeVote $bureauDeVote)
    {
        $bureauDeVote->delete();

        return redirect()->route('admin.geo.bureaux-de-vote.index')
            ->with('success', 'Bureau de vote supprimé avec succès');
    }
}