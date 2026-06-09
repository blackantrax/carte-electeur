<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Mail\ContactFormSubmitted;
use App\Http\Requests\ContactRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    /**
     * Affiche la page de contact
     */
    public function index()
    {
        return Inertia::render('Contact', [
            'title' => 'Contactez-nous',
            'description' => 'Formulaire de contact pour les cartes d\'électeurs',
            'meta' => [
                'title' => 'Contactez-nous - Cartes d\'Électeurs',
                'description' => 'Contactez notre équipe pour toute question concernant les cartes d\'électeurs'
            ]
        ]);
    }

    /**
     * Traite l'envoi du formulaire de contact
     */
    public function store(ContactRequest $request)
    {
        // Enregistrement en base de données
        $contact = Contact::create($request->validated());
        
        try {
            // Envoi de l'email
            Mail::to(config('mail.contact_to'))
                ->send(new ContactFormSubmitted($contact));
                
            return redirect()->back()
                ->with('success', 'Merci pour votre message! Nous vous contacterons bientôt.');
                
        } catch (\Exception $e) {
            // En cas d'erreur d'envoi d'email
            \Log::error('Erreur envoi email contact: '.$e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Votre message a été enregistré mais une erreur est survenue lors de l\'envoi.');
        }
    }

    /**
     * Affiche l'interface d'administration des contacts
     */
    public function adminIndex(Request $request)
    {
        $filters = $request->only(['search', 'status', 'trashed']);
        $perPage = $request->input('perPage') ?? 10;

        return Inertia::render('Dashboard/contacts/index', [
            'filters' => $filters,
            'contacts' => Contact::query()
                ->when($filters['search'] ?? null, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%')
                          ->orWhere('email', 'like', '%'.$search.'%')
                          ->orWhere('subject', 'like', '%'.$search.'%')
                          ->orWhere('message', 'like', '%'.$search.'%');
                    });
                })
                ->when($filters['status'] ?? null, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                    if ($trashed === 'with') {
                        $query->withTrashed();
                    } elseif ($trashed === 'only') {
                        $query->onlyTrashed();
                    }
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString(),
            'stats' => [
                'total' => Contact::count(),
                'read' => Contact::where('status', 'read')->count(),
                'unread' => Contact::where('status', 'unread')->count(),
                'archived' => Contact::where('status', 'archived')->count(),
            ]
        ]);
    }

    /**
     * Affiche les détails d'un contact
     */
    public function show(Contact $contact)
    {
        // Marquer comme lu lors de la visualisation
        if ($contact->status === 'unread') {
            $contact->update(['status' => 'read']);
        }

        return Inertia::render('Admin/Contacts/Show', [
            'contact' => $contact
        ]);
    }

    /**
     * Marque un contact comme lu
     */
    public function markAsRead(Contact $contact)
    {
        $contact->update(['status' => 'read']);

        return redirect()->back()
            ->with('success', 'Message marqué comme lu.');
    }

    /**
     * Archive un contact
     */
    public function archive(Contact $contact)
    {
        $contact->update(['status' => 'archived']);

        return redirect()->back()
            ->with('success', 'Message archivé.');
    }

    /**
     * Exporte les contacts au format CSV
     */
    public function export(): StreamedResponse
    {
        $fileName = 'contacts-' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $contacts = Contact::all();
        $columns = ['name', 'email', 'subject', 'message', 'status', 'created_at'];

        $callback = function() use ($contacts, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($contacts as $contact) {
                $row = [];
                foreach ($columns as $column) {
                    $row[] = $contact->$column;
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Supprime un contact
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')
            ->with('success', 'Message supprimé.');
    }

    /**
     * API: Liste des contacts pour l'interface admin
     */
    public function apiIndex(Request $request)
    {
        return Contact::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                      ->orWhere('email', 'like', '%'.$search.'%')
                      ->orWhere('subject', 'like', '%'.$search.'%');
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($request->input('sortBy', 'created_at'), $request->input('sortDirection', 'desc'))
            ->paginate($request->input('perPage', 10));
    }

    /**
     * API: Met à jour le statut d'un contact
     */
    public function updateStatus(Contact $contact, Request $request)
    {
        $request->validate([
            'status' => 'required|in:read,unread,archived'
        ]);

        $contact->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Statut mis à jour',
            'contact' => $contact
        ]);
    }
}