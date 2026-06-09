<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Categorie;
use Inertia\Inertia;
use Carbon\Carbon;

class EventController extends Controller
{

    public function allItemsEvents(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 20); // Nombre d'événements par page (défaut: 20)
            $search = $request->query('search');
            $category = $request->query('category');
            $year = $request->query('year');

            // Construire la requête de base
            $query = Event::with('category')
                ->where('status', 'published')
                ->orderBy('date', 'desc'); // Trier par date descendante

            // Appliquer les filtres
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
                });
            }

            if (!empty($category)) {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('id', $category);
                });
            }

            if (!empty($year) && preg_match('/^\d{4}$/', $year)) {
                $query->whereYear('date', '=', $year);
            }

            // Paginer les résultats
            $events = $query->paginate($perPage);

            // Transformer les événements pour inclure les données nécessaires
            $transformedEvents = $events->getCollection()->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'slug' => $event->slug,
                    'date' => $event->date ? Carbon::parse($event->date)->format('Y-m-d') : null,
                    'location' => $event->location,
                    'url' => $event->url,
                    'description' => $event->description,
                    'status' => $event->status,
                    'category' => $event->category ? [
                        'id' => $event->category->id,
                        'name' => $event->category->name,
                    ] : null,
                ];
            });

            // Réassigner la collection transformée à la pagination
            $events->setCollection($transformedEvents);

            // Retourner la réponse JSON
            return response()->json([
                'events' => $events
            ]);
        } catch (\Exception $e) {
            // Gérer les erreurs
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la récupération des événements.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function allEvents()
    {
        return Inertia::render('All/events', [
            'title' => 'Event Publiés',
            'description' => 'Liste des events publiés.',
        ]);
    }


    /**
     * Récupérer les dernières evenements.
     */
    public function latest()
    {
        $events = Event::with('category') // Charger la relation category
            ->where('status', 'published') // Filtrer les events publiées
            ->orderBy('created_at', 'desc') // Trier par date décroissante
            ->limit(3) // Limiter à 3 résultats
            ->get();
        return response()->json(['events' => $events]);
    }

    public function index()
    {
        $events = Event::with('category')
            ->orderBy('created_at', 'desc')
            ->get(); // 🔥 Correction ici
        return Inertia::render('Dashboard/events/index', [
            'events' => $events
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/events/create', [
            'title' => 'Créer un Event',
            'description' => 'Ajoutez un nouvel Event au blog.',
            'categories' => Categorie::where('type', 'evenement')->get()
        ]);
    }

   public function store(Request $request)
{
    // Validation des données
    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'slug' => 'required|string|unique:events,slug',
        'category_id' => 'required|exists:categories,id',
        'date' => 'required|date',
        'location' => 'required|string|max:255',
        'url' => 'required|url',
        'description' => 'required|string',
        'status' => 'required|in:draft,published',
        'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
    ]);

    try {
        // Gestion de l'image
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('events/featured-images', 'public');
            $validatedData['featured_image'] = $path;
        }

        // Création de l'événement
        $event = Event::create($validatedData);

        // Redirection avec message de succès
        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Événement créé avec succès.');

    } catch (\Exception $e) {
        // En cas d'erreur, suppression de l'image uploadée si nécessaire
        if (isset($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        // Retour vers le formulaire avec les erreurs
        return back()
            ->withInput()
            ->withErrors([
                'error' => 'Une erreur est survenue lors de la création de l\'événement.',
                'exception' => $e->getMessage()
            ]);
    }
}



    public function show(Event $event)
    {
        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        return Inertia::render('Dashboard/events/edit', [
            'title' => 'Éditer un Event',
            'description' => 'Modifiez les informations de l\'événement.',
            'event' => $event->load('category'),
            'categories' => Categorie::where('type', 'evenement')->get()
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $event->update($request->all());

        if ($request->hasFile('image')) {
            $event->image = $request->file('image')->store('events', 'public');
            $event->save();
        }

        return redirect()->route('admin.events.index')->with('success', 'Événement mis à jour.');
    }

    public function destroy($id)
{
    try {
        $event = Event::findOrFail($id);
        $event->delete();
        
        return redirect()->route('admin.events.index')
               ->with('success', 'Événement supprimé avec succès');
    } catch (\Exception $e) {
        return back()->with('error', 'Événement introuvable ou déjà supprimé');
    }
}

public function updateStatus(Event $event, Request $request)
{
    $validated = $request->validate([
        'status' => 'required|in:draft,published,archived'
    ]);
    
    $event->update($validated);

    return redirect()
            ->route('admin.events.index')
            ->with('success', 'Statut mis à jour.');
}
}
