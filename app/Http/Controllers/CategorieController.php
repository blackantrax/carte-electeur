<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategorieController extends Controller
{
    /**
     * Affiche la liste des catégories.
     */
    public function index()
    {
        return Inertia::render('Dashboard/categories/index', [
            'categories' => Categorie::orderBy('name')->get()
        ]);
    }

    /**
     * Récupère les catégories pour le rechargement.
     */
    public function loading()
    {
        try {
            $data = Categorie::all()->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'slug' => $item->slug
                ];
            });

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur de chargement',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Stocke une nouvelle catégorie.
     */
    public function store(Request $request)
    {
        // ✅ Validation des données
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:article,photo,video,publication,evenement',
        ]);

        // ✅ Création de la catégorie
        Categorie::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
        ]);

        // ✅ Redirection correcte pour Inertia
        return redirect()->route('categories.index')->with('success', 'Catégorie ajoutée avec succès.');
    }

    public function getCategoriesByType($type)
    {
        $categories = Categorie::where('type', $type)->orderBy('name')->get();

        if ($categories->isEmpty()) {
            return response()->json(['message' => 'Aucune catégorie trouvée pour ce type.'], 404);
        }

        return response()->json($categories);
    }


    /**
     * Affiche une catégorie spécifique.
     */
    public function show(Categorie $categorie)
    {
        return response()->json($categorie);
    }

    /**
     * Met à jour une catégorie existante.
     */
    public function update(Request $request, $id)
    {
        // Récupérer la catégorie par son ID
        $categorie = Categorie::find($id);

        // Vérifier si la catégorie existe
        if (!$categorie) {
            return response()->json([
                'message' => 'Catégorie non trouvée.',
            ], 404);
        }

        // ✅ Validation des données
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categorie->id),
            ],
            'type' => 'required|in:article,photo,video,publication,evenement',
        ]);

        // ✅ Mise à jour uniquement si nécessaire
        if ($categorie->name !== $request->name || $categorie->type !== $request->type) {
            $categorie->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'type' => $request->type,
            ]);
        }

        // ✅ Renvoyer une réponse JSON
        return response()->json([
            'message' => 'Catégorie mise à jour avec succès.',
            'categorie' => $categorie,
        ]);
    }


    /**
     * Supprime une catégorie.
     */
    public function destroy($id)
    {
        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json(['error' => 'Catégorie non trouvée.'], 404);
        }

        try {
            $categorie->delete();
            return response()->json(['message' => 'Catégorie supprimée avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['error' => "Erreur lors de la suppression."], 500);
        }
    }

}
