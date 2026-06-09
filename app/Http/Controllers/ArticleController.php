<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Categorie;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Dashboard/articles/index', [
            'articles' => Article::with('category')
                ->orderBy('title')
                ->get()
                ->map(function ($article) {
                    return [
                        'id' => $article->id,
                        'title' => $article->title,
                        'slug' => $article->slug,
                        'content' => $article->content,
                        'status' => $article->status ?? 'draft',
                        'featured_image' => $article->featured_image_url, // Accesseur pour l'image
                        'category' => $article->category ? [
                            'id' => $article->category->id,
                            'name' => $article->category->name,
                        ] : null,
                    ];
                }),
            'title' => 'Articles Now',
            'description' => 'This is the Articles page content.',
        ]);
    }

    public function allItemsArticles(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 20); // Nombre d'articles par page (défaut: 20)
            $search = $request->query('search');
            $category = $request->query('category');
            $year = $request->query('year');

            // Construire la requête de base
            $query = Article::with('category')
                ->where('status', 'published')
                ->orderBy('title');

            // Appliquer les filtres
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('content', 'like', '%' . $search . '%');
                });
            }

            if ($category) {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('id', $category); // Utilisation de 'id' au lieu de 'name'
                });
            }

            if (!empty($year) && preg_match('/^\d{4}$/', $year)) {
                $query->whereYear('created_at', '=', $year);
            }

            // Paginer les résultats
            $articles = $query->paginate($perPage);

            // Transformer les articles pour inclure les données nécessaires
            $transformedArticles = $articles->getCollection()->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'content' => $article->content,
                    'status' => $article->status,
                    'featured_image' => $article->featured_image_url,
                    'created_at' => $article->created_at->format('Y-m-d H:i:s'),
                    'category' => $article->category ? [
                        'id' => $article->category->id,
                        'name' => $article->category->name,
                    ] : null,
                ];
            });

            // Réassigner la collection transformée à la pagination
            $articles->setCollection($transformedArticles);

            // Retourner la réponse JSON
            return response()->json([
                'articles' => $articles
            ]);
        } catch (\Exception $e) {
            // Gérer les erreurs
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la récupération des articles.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function allArticles()
    {
        return Inertia::render('All/articles', [
            'title' => 'Articles Publiés',
            'description' => 'Liste des articles publiés.',
        ]);
    }

    /**
     * Display only published articles.
     */
    public function publishedArticles(Request $request)
    {
        $published = filter_var($request->query('published', true), FILTER_VALIDATE_BOOLEAN);
        $articles = Article::with('category')
            ->where('status', $published ? 'published' : 'draft')
            ->orderBy('title')
            ->get();
        if ($articles->isEmpty()) {
            return response()->json([
                'message' => 'Aucun article publié trouvé',
                'articles' => [],
            ], 200); // 200 pour signifier que la requête a réussi, mais pas de données.
        }

        return response()->json([
            'articles' => $articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'content' => $article->content,
                    'status' => $article->status,
                    'featured_image' => $article->featured_image_url, // Accesseur pour l'image
                    'category' => $article->category ? [
                        'id' => $article->category->id,
                        'name' => $article->category->name,
                    ] : null,
                ];
            }),
            'title' => 'Articles Publiés',
            'description' => 'Liste des articles publiés.',
        ]);
    }


    public function featuredArticles()
    {
        return response()->json([
            'articles' => Article::with('category')
                ->where(function ($query) {
                    $query->where('main', true)->orWhere('secondary', true);
                })
                ->orderBy('title')
                ->get()
                ->map(function ($article) {
                    return [
                        'id' => $article->id,
                        'title' => $article->title,
                        'slug' => $article->slug,
                        'content' => $article->content,
                        'status' => $article->status,
                        'featured_image' => $article->featured_image_url, // Accesseur pour l'image
                        'category' => $article->category ? [
                            'id' => $article->category->id,
                            'name' => $article->category->name,
                        ] : null,
                    ];
                }),
            'title' => 'Articles Mis en Avant',
            'description' => 'Liste des articles principaux et secondaires.',
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/articles/create', [
            'title' => 'Créer un Article',
            'description' => 'Ajoutez un nouvel article au blog.',
            'categories' => Categorie::where('type', 'article')->get()
        ]);
    }

    // ArticlesController.php
    public function edit(Article $article)
    {
        return inertia('Dashboard/articles/edit', [
            'article' => $article->toArray(),
            'categories' => Categorie::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Correction: Utilisez 'category_id' au lieu de 'category'
    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:articles,slug',
        'category_id' => 'required|exists:categories,id', // ← ICI le changement
        'content' => 'required|string',
        'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'status' => 'required|in:draft,published,archived',
    ]);

    // Création de l'article (gardez category_id ici aussi)
    $article = Article::create([
        'title' => $validatedData['title'],
        'slug' => $validatedData['slug'],
        'category_id' => $validatedData['category_id'], // ← ICI aussi
        'content' => $validatedData['content'],
        'featured_image' => $request->file('featured_image')?->store('featured_images', 'public'),
        'status' => $validatedData['status'],
    ]);

    return redirect()->route('articles.index')->with('success', 'Article créé!');
}

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $article = Article::with('category')->where('slug', $slug)->firstOrFail();

        return Inertia::render('Articles/show', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status,
                'featured_image' => $article->featured_image_url,
                'category' => $article->category ? [
                    'id' => $article->category->id,
                    'name' => $article->category->name,
                ] : null,
            ],
            'title' => $article->title,
            'description' => 'Détail de l’article',
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $article = Article::findOrFail($id);

        // Validation des données
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => "required|string|max:255|unique:articles,slug,{$article->id}",
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
            'featuredImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'status' => 'required|in:draft,published,archived',
            'featured' => 'boolean',
            'allow_comments' => 'boolean',
        ]);

        // Gestion de l'image
        if ($request->hasFile('featuredImage')) {
            // Supprimer l'ancienne image si elle existe
            if ($article->featured_image) {
                Storage::disk('public')->delete($article->featured_image);
            }
            
            $imagePath = $request->file('featuredImage')->store('featured_images', 'public');
            $validatedData['featured_image'] = $imagePath;
        }

        // Mise à jour de l'article
        $article->update([
            'title' => $validatedData['title'],
            'slug' => $validatedData['slug'],
            'category_id' => $validatedData['category_id'],
            'content' => $validatedData['content'],
            'status' => $validatedData['status'],
            'featured' => $request->boolean('featured'),
            'allow_comments' => $request->boolean('allow_comments'),
            'featured_image' => $validatedData['featured_image'] ?? $article->featured_image,
        ]);

        return redirect()->route('articles.index')->with('success', 'Article mis à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return redirect()->route('articles.index')->with('success', 'Article mis à jour avec succès');
    }

    public function updateArticleStatus(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:articles,id',
            'status' => 'required|in:main,secondary,normal',
        ]);

        $article = Article::findOrFail($validatedData['id']);

        // Mise à jour du statut principal et secondaire
        if ($validatedData['status'] === 'main') {
            // Réinitialiser tous les autres articles principaux
            Article::where('main', true)->update(['main' => false]);

            $article->update([
                'main' => true,
                'secondary' => false,
            ]);
        } elseif ($validatedData['status'] === 'secondary') {
            // Vérifier si la limite de 3 articles secondaires est atteinte
            if (Article::where('secondary', true)->count() >= 3) {
                return response()->json(['message' => 'Limite de 3 articles secondaires atteinte'], 400);
            }

            $article->update([
                'main' => false,
                'secondary' => true,
            ]);
        } else {
            // Réinitialiser les statuts
            $article->update([
                'main' => false,
                'secondary' => false,
            ]);
        }

        return response()->json([
            'message' => 'Statut de l\'article mis à jour avec succès',
            'article' => $article,
        ]);
    }

    public function updateStatus(Request $request, Article $article)
    {
        $request->validate([
            'status' => 'required|in:published,draft,archived' // Adaptez selon vos besoins
        ]);

        DB::beginTransaction();
        try {
            // Logique métier si nécessaire (ex: vérification des limites)
            if ($request->status === 'published') {
                $alreadyPublishedCount = Article::where('status', 'published')->count();
                if ($alreadyPublishedCount >= 10) { // Exemple de limite
                    return response()->json([
                        'message' => 'Limite de 10 articles publiés atteinte'
                    ], 422);
                }
            }

            $article->update(['status' => $request->status]);

            DB::commit();
            return redirect()->route('articles.index')->with('success', 'Statut mis à jour avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
