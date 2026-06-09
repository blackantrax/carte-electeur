<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Article;
use App\Models\Event;
use App\Models\Inscription;
use App\Models\Video;
use App\Models\SlideshowImage;
use Illuminate\Support\Facades\Cache;


class DashboardController extends Controller
{
    public function index()
{
    $data = [
        'stats' => [
            'articles' => $this->getArticleStats(),
            'events' => $this->getEventStats(),
            'inscriptions' => $this->getInscriptionStats(),
            'media' => $this->getMediaStats(),
        ],
        'recentActivity' => $this->getRecentActivity(),
        'lastUpdated' => now()->toDateTimeString()
    ];
    return Inertia::render('Dashboard/index', $data);
}

    protected function getTrendPercentage($model)
    {
        $current = $model::whereDate('created_at', '>=', now()->subWeek())->count();
        $previous = $model::whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->count();
        
        if ($previous === 0) return 0; // Éviter la division par zéro
        
        return round((($current - $previous) / $previous) * 100);
    }

    protected function getArticleStats()
    {
        return [
            'total' => Article::count(),
            'published' => Article::published()->count(),
            'change' => $this->getTrendPercentage(Article::class),
            'pending' => Article::where('status', 'pending')->count(),
            'latest' => Article::latest()->take(3)->get()->map->only('id', 'title', 'status', 'created_at'),
            'trend' => $this->getTrend(Article::class),
            'chart' => [
                'labels' => ['Publiés', 'En attente', 'Brouillons'],
                'datasets' => [[
                    'data' => [
                        Article::published()->count(),
                        Article::where('status', 'pending')->count(),
                        Article::where('status', 'draft')->count()
                    ],
                    'backgroundColor' => ['#10B981', '#F59E0B', '#EF4444']
                ]]
            ]
        ];
    }

    protected function getEventStats()
    {
        return [
            'total' => Event::count(),
            'upcoming' => Event::upcoming()->count(),
            'past' => Event::past()->count(),
            'latest' => Event::latest()->take(3)->get()->map->only('id', 'title', 'date', 'status'),
            'trend' => $this->getTrend(Event::class),
            'chart' => [
                'labels' => ['À venir', 'Passés'],
                'datasets' => [[
                    'data' => [
                        Event::upcoming()->count(),
                        Event::past()->count()
                    ],
                    'backgroundColor' => ['#3B82F6', '#6B7280']
                ]]
            ]
        ];
    }

    protected function getInscriptionStats()
    {
        return [
            'total' => Inscription::count(),
            'recent' => Inscription::recent()->count(),
            'trend' => $this->getTrend(Inscription::class),
            'chart' => [
                'labels' => ['7 derniers jours', 'Total'],
                'datasets' => [[
                    'data' => [
                        Inscription::recent()->count(),
                        Inscription::count()
                    ],
                    'backgroundColor' => ['#8B5CF6', '#EC4899']
                ]]
            ]
        ];
    }

    protected function getMediaStats()
    {
        return [
            'videos' => Video::count(),
            'slides' => SlideshowImage::count(),
            'trend' => $this->getTrend(Video::class),
            'chart' => [
                'labels' => ['Vidéos', 'Slides'],
                'datasets' => [[
                    'data' => [
                        Video::count(),
                        SlideshowImage::count()
                    ],
                    'backgroundColor' => ['#6366F1', '#F97316']
                ]]
            ]
        ];
    }

    protected function getRecentActivity()
{
    return Cache::remember('recent_activity', 300, function () {
        $articles = Article::latest()->take(2)->get()
            ->map(function ($item) {
                return [
                    'type' => 'article',
                    'icon' => 'bi-file-earmark-text',
                    'title' => $item->title,
                    'action' => $this->getArticleStatus($item->status),
                    'time' => $item->updated_at->diffForHumans(),
                    'url' => route('articles.edit', $item->id)
                ];
            })->toArray(); // Convertir en tableau

        $events = Event::latest()->take(2)->get()
            ->map(function ($item) {
                return [
                    'type' => 'event',
                    'icon' => 'bi-calendar-event',
                    'title' => $item->title,
                    'action' => $item->date > now() ? 'À venir' : 'Terminé',
                    'time' => $item->updated_at->diffForHumans(),
                    'url' => route('admin.events.edit', $item->id)
                ];
            })->toArray(); // Convertir en tableau

        // Fusionner les tableaux et trier
        $merged = array_merge($articles, $events);
        
        // Trier par date décroissante
        usort($merged, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($merged, 0, 4); // Prendre les 4 premiers
    });
}

    protected function getTrend($model)
    {
        $current = $model::whereDate('created_at', '>=', now()->subWeek())->count();
        $previous = $model::whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])->count();

        if ($current > $previous) return 'up';
        if ($current < $previous) return 'down';
        return 'stable';
    }

    protected function getArticleStatus($status)
    {
        return [
            'published' => 'Publié',
            'pending' => 'En attente',
            'draft' => 'Brouillon'
        ][$status] ?? $status;
    }

    public function statistics(Request $request)
    {
        $filters = [
            'period' => $request->input('period', 'week'),
            'type' => $request->input('type', 'all'),
            'status' => $request->input('status', 'all')
        ];

        return Inertia::render('Statistics/Index', [
            'initialStats' => $this->getFilteredStats($filters),
            'initialChartData' => $this->getChartData($filters),
            'initialTableData' => $this->getTableData($filters),
            'filters' => $filters
        ]);
    }

    protected function getFilteredStats(array $filters)
    {
        return [
            'articles' => [
                'total' => Article::applyFilters($filters)->count(),
                'change' => $this->getChangePercentage(Article::class, $filters),
                'chart' => $this->getDailyCounts(Article::class, $filters)
            ],
            'events' => [
                'total' => Event::applyFilters($filters)->count(),
                'change' => $this->getChangePercentage(Event::class, $filters),
                'chart' => $this->getDailyCounts(Event::class, $filters)
            ],
            'views' => [
                'total' => ViewLog::applyFilters($filters)->count(),
                'change' => $this->getChangePercentage(ViewLog::class, $filters),
                'chart' => $this->getDailyCounts(ViewLog::class, $filters)
            ],
            'engagement' => [
                'total' => Comment::applyFilters($filters)->count() + Like::applyFilters($filters)->count(),
                'change' => $this->getEngagementChange($filters),
                'chart' => $this->getEngagementChart($filters)
            ]
        ];
    }
}