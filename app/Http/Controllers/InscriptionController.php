<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InscriptionController extends Controller
{
    public function adminIndex(){
        return inertia('Dashboard/Statistics/electors');
    }
    public function index()
    {
        $stats = Inscription::select('departement', DB::raw('COUNT(*) as count'))
            ->groupBy('departement')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
    }

    public function store(Request $request)
    {
        // Conversion explicite de consentement en boolean si envoyé en tant que string
        $request->merge([
            'consentement' => filter_var($request->consentement, FILTER_VALIDATE_BOOLEAN)
        ]);

        // Validation des données reçues
        $validated = $request->validate([
            'nom' => 'required|string|min:2',
            'prenom' => 'required|string',
            'telephone' => ['required', 'regex:/^(237|00237|\\+237)?[23678]\\d{8}$/'],
            'email' => 'required|email|unique:inscriptions,email',
            'age' => 'required|integer|min:18|max:120',
            'statut_professionnel' => 'required|string',
            'identification' => 'required|string',
            'departement' => 'required|string',
            'inscrit_liste' => 'required|string',
            'numero_elecam' => 'nullable|string|max:4',
            'annee_inscription' => 'required|integer|min:2000|max:' . date('Y'),
            'centre_vote' => 'required|string',
            'antenne_communale' => 'required|string',
            'consentement' => 'required|boolean',
        ]);

        try {
            Inscription::create($validated);

            return response()->json([
                'message' => 'Inscription réussie !'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'inscription.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}

