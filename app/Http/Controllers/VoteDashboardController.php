<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BureauDeVote;
use App\Models\Inscription;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Departement;
use App\Models\Arrondissement;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoteDashboardController extends Controller
{
    /**
     * Affiche le tableau de bord électoral
     */
    public function index()
    {
        // Statistiques principales
        $stats = [
            'total_bureaux' => BureauDeVote::count(),
            'total_inscriptions' => Inscription::count(),
            'inscriptions_pending' => Inscription::where('status', 'pending')->count(),
            'inscriptions_verified' => Inscription::where('status', 'verified')->count(),
            'inscriptions_rejected' => Inscription::where('status', 'rejected')->count(),
        ];

        // Dernières inscriptions
        $latestInscriptions = Inscription::with(['commune.departement.region', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // Bureaux avec le plus d'inscriptions
        $topBureaux = BureauDeVote::withCount(['inscriptions' => function($query) {
            $query->where('status', 'VALcodeE');
        }])
        ->orderByDesc('inscriptions_count')
        ->take(5)
        ->get();

        // Répartition par région
        $byRegion = Inscription::select([
            'regions.nom as region_name',
            DB::raw('COUNT(inscriptions.code) as inscriptions_count')
        ])
        ->join('communes', 'inscriptions.commune_code', '=', 'communes.code')
        ->join('departements', 'communes.departement_code', '=', 'departements.code')
        ->join('regions', 'departements.region_code', '=', 'regions.code')
        ->where('inscriptions.status', 'VALcodeE')
        ->groupBy('regions.nom')
        ->get();

        return inertia('Dashboard/votes/index', [
            'stats' => $stats,
            'latestInscriptions' => $latestInscriptions,
            'topBureaux' => $topBureaux,
            'byRegion' => $byRegion,
            'pendingRegistrations' => $stats['inscriptions_pending']
        ]);
    }

    /**
     * Tableau de bord géographique
     */
    public function geos()
    {
        // Statistiques de base
        $stats = [
            'pays' => Pays::count(),
            'regions' => Region::count(),
            'departements' => Departement::count(),
            'arrondissements' => Arrondissement::count(),
            'communes' => Commune::count(),
            'bureaux' => BureauDeVote::count(),
        ];

        // Calcul des tendances (comparaison avec le mois précédent)
        $trends = [
            'pays' => $this->calculateTrend(Pays::class),
            'regions' => $this->calculateTrend(Region::class),
            'departements' => $this->calculateTrend(Departement::class),
            'arrondissements' => $this->calculateTrend(Arrondissement::class),
            'bureaux' => $this->calculateTrend(BureauDeVote::class),
        ];

        // Données pour la carte (niveau régional par défaut)
        $mapData = $this->getMapData('regions');

        return inertia('Dashboard/Geo/index', [
            'stats' => $stats,
            'trends' => $trends,
            'mapData' => $mapData
        ]);
    }

    /**
     * API pour les données de la carte par niveau
     */
    public function mapData(Request $request)
    {
        $level = $request->input('level', 'regions');
        $data = $this->getMapData($level);

        return response()->json([
            'mapData' => $data
        ]);
    }

    /**
     * Calcule la tendance mensuelle pour une entité
     */
    private function calculateTrend(string $modelClass): float
    {
        $currentMonthCount = $modelClass::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])->count();

        $lastMonthCount = $modelClass::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->count();

        if ($lastMonthCount === 0) {
            return 0;
        }

        return (($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100;
    }

    /**
     * Récupère les données pour la carte selon le niveau
     */
    private function getMapData(string $level): array
    {
        switch ($level) {
            case 'departements':
                return Departement::with('region')
                    ->select([
                        'departements.code',
                        'departements.nom',
                        'departements.region_code',
                        DB::raw('COUNT(bureau_de_votes.code) as bureaux_count')
                    ])
                    ->leftJoin('communes', 'communes.departement_code', '=', 'departements.code')
                    ->leftJoin('bureau_de_votes', 'bureau_de_votes.commune_code', '=', 'communes.code')
                    ->groupBy('departements.code', 'departements.nom', 'departements.region_code')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'code' => $item->code,
                            'name' => $item->nom,
                            'region' => $item->region->nom,
                            'bureaux_count' => $item->bureaux_count,
                            'path' => $item->geojson_path // Supposons que vous stockez le chemin du GeoJSON
                        ];
                    })
                    ->toArray();

            case 'communes':
                return Commune::with('departement.region')
                    ->select([
                        'communes.code',
                        'communes.nom',
                        'communes.departement_code',
                        DB::raw('COUNT(bureau_de_votes.code) as bureaux_count')
                    ])
                    ->leftJoin('bureau_de_votes', 'bureau_de_votes.commune_code', '=', 'communes.code')
                    ->groupBy('communes.code', 'communes.nom', 'communes.departement_code')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'code' => $item->code,
                            'name' => $item->nom,
                            'departement' => $item->departement->nom,
                            'region' => $item->departement->region->nom,
                            'bureaux_count' => $item->bureaux_count,
                            'path' => $item->geojson_path
                        ];
                    })
                    ->toArray();

            case 'regions':
            default:
                return Region::select([
                        'regions.code',
                        'regions.nom',
                        DB::raw('COUNT(DISTINCT departements.code) as departements_count'),
                        DB::raw('COUNT(DISTINCT communes.code) as communes_count'),
                        DB::raw('COUNT(DISTINCT bureau_de_votes.code) as bureaux_count')
                    ])
                    ->leftJoin('departements', 'departements.region_code', '=', 'regions.code')
                    ->leftJoin('communes', 'communes.departement_code', '=', 'departements.code')
                    ->leftJoin('bureau_de_votes', 'bureau_de_votes.commune_code', '=', 'communes.code')
                    ->groupBy('regions.code', 'regions.nom')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'code' => $item->code,
                            'name' => $item->nom,
                            'departements_count' => $item->departements_count,
                            'communes_count' => $item->communes_count,
                            'bureaux_count' => $item->bureaux_count,
                            'path' => $item->geojson_path
                        ];
                    })
                    ->toArray();
        }
    }

    /**
     * API pour les données du graphique
     */
    public function chartData(Request $request)
    {
        $data = Inscription::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$request->start_date, $request->end_date])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'labels' => $data->pluck('date'),
            'values' => $data->pluck('count')
        ]);
    }

    /**
     * API pour les statistiques en temps réel
     */
    public function liveStats()
    {
        return response()->json([
            'total' => Inscription::count(),
            'verified' => Inscription::where('status', 'verified')->count(),
            'pending' => Inscription::where('status', 'pending')->count(),
            'updated_at' => now()->toDateTimeString()
        ]);
    }
}