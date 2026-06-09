<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Models\Pays;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PaysController extends Controller
{
    public function index(Request $request)
{
    $query = Pays::query();

    // Filtrage uniquement si non vide
    $search = $request->input('search');
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('code', 'LIKE', "%{$search}%")
              ->orWhere('nom', 'LIKE', "%{$search}%")
              ->orWhere('continent', 'LIKE', "%{$search}%");
        });
    }

    $continent = $request->input('continent');
    if (!empty($continent)) {
        $query->where('continent', $continent);
    }

    // Tri
    $sortField = $request->input('sort_field', 'nom');
    $sortDirection = $request->input('sort_direction', 'asc');
    $query->orderBy($sortField, $sortDirection);

    // Pagination
    $pays = $query->paginate(12)->withQueryString();

    // Liste des continents pour les filtres
    $continents = Pays::select('continent')
        ->whereNotNull('continent')
        ->distinct()
        ->orderBy('continent')
        ->pluck('continent');

    return Inertia::render('Dashboard/Geo/Pays/Index', [
        'pays' => $pays,
        'filters' => $request->only(['search', 'continent', 'sort_field', 'sort_direction']),
        'continents' => $continents,
    ]);
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:2|unique:pays',
            'nom' => 'required|string|max:100',
            'continent' => 'required|string|max:50',
            'indicatif_telephonique' => 'required|string|max:5'
        ]);

        Pays::create($validated);

        return back()->with('success', 'Pays créé avec succès');
    }


    public function update(Request $request, $code) // On récupère directement le code
{
    // Trouver le pays manuellement
    $pays = Pays::where('code', $code)->firstOrFail();

    $validated = $request->validate([
        'nom' => [
            'required', 
            'string',
            'max:100',
            Rule::unique('pays')->ignore($pays->code, 'code')
        ],
        'continent' => 'required|string|max:50|in:Afrique,Europe,Asie,Amérique,Océanie',
        'indicatif_telephonique' => 'required|string|max:5|regex:/^\d+$/'
    ]);

    $pays->update($validated);

    return back()->with('success', 'Pays mis à jour avec succès.');
}

    public function destroy($code)
    {
        $pays = Pays::where('code', $code)->firstOrFail();
        $pays->delete();
        return back()->with('success', 'Pays supprimé');
    }
}