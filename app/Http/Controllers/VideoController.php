<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    /**
     * Récupérer les dernières vidéos.
     */
    public function latest()
    {
        $videos = Video::with('category')
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        return response()->json(['videos' => $videos]);
    }

    /**
     * Afficher la liste des vidéos.
     */
    public function index()
    {
        $videos = Video::with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dashboard/videos/index', [
            'videos' => $videos
        ]);
    }

    public function allItemsVideos(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 20);
            $search = $request->query('search');
            $category = $request->query('category');
            $year = $request->query('year');

            $query = Video::with('category')
                ->where('status', 'published')
                ->orderBy('title');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            if ($category) {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('id', $category);
                });
            }

            if (!empty($year) && preg_match('/^\d{4}$/', $year)) {
                $query->whereYear('created_at', '=', $year);
            }

            $videos = $query->paginate($perPage);

            $transformedVideos = $videos->getCollection()->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'slug' => $video->slug,
                    'url' => $video->url,
                    'description' => $video->description,
                    'status' => $video->status,
                    'created_at' => $video->created_at->format('Y-m-d H:i:s'),
                    'category' => $video->category ? [
                        'id' => $video->category->id,
                        'name' => $video->category->name,
                    ] : null,
                ];
            });

            $videos->setCollection($transformedVideos);

            return response()->json([
                'videos' => $videos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la récupération des Videos.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function allVideos()
    {
        return Inertia::render('All/videos', [
            'title' => 'Videos Publiés',
            'description' => 'Liste des videos publiés.',
        ]);
    }

    /**
     * Afficher une vidéo individuelle.
     */
    public function show($id)
    {
        $video = Video::with('category')->findOrFail($id);
        return response()->json(['video' => $video]);
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create()
    {
        $categories = Categorie::where('type', 'video')->get();
        
        return Inertia::render('Dashboard/videos/create', [
            'title' => 'Créer une Vidéo',
            'description' => 'Ajoutez un nouvel video au blog.',
            'categories' => $categories
        ]);
    }

    /**
     * Enregistrer une nouvelle vidéo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:videos,slug',
            'category_id' => 'required|exists:categories,id',
            'url' => 'required|url',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
        ]);

        $video = Video::create($validated);

        return redirect()->route('admin.medias.videos.index')
            ->with('success', 'Vidéo créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition.
     */
    public function edit($id)
    {
        $video = Video::findOrFail($id);
        $categories = Categorie::where('type', 'video')->get();

        return Inertia::render('Dashboard/videos/edit', [
            'video' => $video,
            'categories' => $categories,
            'title' => 'Modifier la vidéo: ' . $video->title,
        ]);
    }

    /**
     * Mettre à jour une vidéo.
     */
    public function update(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:videos,slug,' . $video->id,
            'category_id' => 'required|exists:categories,id',
            'url' => 'required|url',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
        ]);

        $video->update($validated);

        return redirect()->route('admin.medias.videos.index')
            ->with('success', 'Vidéo mise à jour avec succès.');
    }

    /**
     * Mettre à jour le statut d'une vidéo.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,published,archived'
        ]);

        $video = Video::findOrFail($id);
        $video->status = $request->status;
        $video->save();

        return redirect()->route('admin.medias.videos.index')
            ->with('success', 'Statut mis à jour avec succès');
    }

    /**
     * Supprimer une vidéo.
     */
    public function destroy($id)
    {
        $video = Video::findOrFail($id);
        $video->delete();

        return redirect()->route('admin.medias.videos.index')
            ->with('success', 'Vidéo supprimée avec succès');
    }

    /**
     * Actions groupées sur les vidéos.
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|in:delete,publish,draft,archive'
        ]);

        $count = 0;

        switch ($request->action) {
            case 'delete':
                $count = Video::whereIn('id', $request->ids)->delete();
                break;
            case 'publish':
                $count = Video::whereIn('id', $request->ids)->update(['status' => 'published']);
                break;
            case 'draft':
                $count = Video::whereIn('id', $request->ids)->update(['status' => 'draft']);
                break;
            case 'archive':
                $count = Video::whereIn('id', $request->ids)->update(['status' => 'archived']);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => "Action effectuée sur $count vidéo(s)"
        ]);
    }
}