<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slideshow;
use App\Models\Categorie;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\SlideshowImage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SlideshowController extends Controller
{
    public function allItemsSlideshows(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 20);
            $search = $request->query('search');
            $category = $request->query('category');
            $year = $request->query('year');

            $query = Slideshow::with(['category', 'images'])
                ->where('status', 'published')
                ->orderBy('title');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%');
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

            $slideshows = $query->paginate($perPage);

            $transformedSlideshows = $slideshows->getCollection()->map(function ($slideshow) {
                return [
                    'id' => $slideshow->id,
                    'title' => $slideshow->title,
                    'slug' => $slideshow->slug,
                    'status' => $slideshow->status,
                    'created_at' => $slideshow->created_at->format('Y-m-d H:i:s'),
                    'category' => $slideshow->category ? [
                        'id' => $slideshow->category->id,
                        'name' => $slideshow->category->name,
                    ] : null,
                    'images' => $slideshow->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'url' => $image->url,
                            'description' => $image->description,
                        ];
                    }),
                ];
            });

            $slideshows->setCollection($transformedSlideshows);

            return response()->json([
                'slideshows' => $slideshows
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la récupération des slideshows.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function allSlideshows()
    {
        return Inertia::render('All/slideshows', [
            'title' => 'Diaporamas Publiés',
            'description' => 'Liste des diaporamas publiés.',
        ]);
    }

    public function latest()
    {
        $slideshows = Slideshow::with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $slideshows->each(function ($slideshow) {
            $slideshow->images->each(function ($image) {
                $image->url = asset('storage/' . $image->path);
            });
        });

        return response()->json(['slideshows' => $slideshows]);
    }

    public function index()
    {
        $slideshows = Slideshow::with(['category', 'images'])->latest()->get();

        $slideshows->each(function ($slideshow) {
            $slideshow->images->each(function ($image) {
                $image->url = asset('storage/' . $image->path);
            });
        });

        return Inertia::render('Dashboard/slideshows/index', [
            'slideshows' => $slideshows
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,published,archived'
        ]);

        $slideshow = Slideshow::findOrFail($id);
        $slideshow->status = $request->status;
        $slideshow->save();

        return redirect()->route('admin.medias.slideshows.index')
            ->with('success', 'Statut mis à jour avec succès');
    }

    public function create()
    {
        $categories = Categorie::where('type', 'slideshow')->get();

        return Inertia::render('Dashboard/slideshows/create', [
            'title' => 'Créer une SlideShow',
            'description' => 'Ajoutez un nouvel slideshow au blog.',
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:slideshows,slug|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'images' => 'required|array|min:1',
            'images.*' => 'required|file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'descriptions' => 'required|array|min:1',
            'descriptions.*' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,archived'
        ]);

        DB::beginTransaction();

        try {
            $slideshow = Slideshow::create([
                'title' => $request->title,
                'slug' => $request->slug,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('slideshows', 'public');

                SlideshowImage::create([
                    'slideshow_id' => $slideshow->id,
                    'path' => $path,
                    'description' => $request->descriptions[$index] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.medias.slideshows.index')
                ->with('success', 'Diaporama créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création du diaporama: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $slideshow = Slideshow::with(['category', 'images'])->findOrFail($id);
        $categories = Categorie::where('type', 'slideshow')->get();

        // Transformer les images pour inclure les URLs complètes
        $slideshow->images->each(function ($image) {
            $image->url = asset('storage/' . $image->path);
        });

        return Inertia::render('Dashboard/slideshows/edit', [
            'title' => 'Modifier le diaporama',
            'description' => 'Modifiez les détails de ce diaporama.',
            'slideshow' => $slideshow,
            'categories' => $categories,
            'existingImages' => $slideshow->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => asset('storage/' . $image->path),
                    'description' => $image->description,
                ];
            })
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:slideshows,slug,' . $id,
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'new_images' => 'nullable|array',
            'new_images.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'image_descriptions' => 'nullable|array',
            'image_descriptions.*' => 'nullable|string|max:500',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'nullable|integer',
            'status' => 'required|in:draft,published,archived'
        ]);

        DB::beginTransaction();

        try {
            $slideshow = Slideshow::findOrFail($id);
            $slideshow->update([
                'title' => $request->title,
                'slug' => $request->slug,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            // Supprimer les images marquées pour suppression
            if ($request->deleted_images) {
                $imagesToDelete = SlideshowImage::whereIn('id', $request->deleted_images)
                    ->where('slideshow_id', $slideshow->id)
                    ->get();

                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
            }

            // Ajouter les nouvelles images
            if ($request->new_images) {
                foreach ($request->new_images as $index => $image) {
                    $path = $image->store('slideshows', 'public');

                    SlideshowImage::create([
                        'slideshow_id' => $slideshow->id,
                        'path' => $path,
                        'description' => $request->image_descriptions[$index] ?? null,
                    ]);
                }
            }

            // Mettre à jour les descriptions des images existantes
            if ($request->image_descriptions) {
                foreach ($request->image_descriptions as $imageId => $description) {
                    if (is_numeric($imageId)) { // Seulement pour les images existantes
                        SlideshowImage::where('id', $imageId)
                            ->where('slideshow_id', $slideshow->id)
                            ->update(['description' => $description]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.medias.slideshows.index')
                ->with('success', 'Diaporama mis à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la mise à jour du diaporama: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $slideshow = Slideshow::with('images')->findOrFail($id);

            // Supprimer les images du stockage
            foreach ($slideshow->images as $image) {
                Storage::disk('public')->delete($image->path);
            }

            // Supprimer le diaporama et ses images en cascade
            $slideshow->delete();

            DB::commit();

            return redirect()->route('admin.medias.slideshows.index')
                ->with('success', 'Diaporama supprimé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression du diaporama: ' . $e->getMessage());
        }
    }
}