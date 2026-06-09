<?php

use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\PublicationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\SlideshowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Geo\RegionController;
use App\Http\Controllers\Geo\DepartementController;
use App\Http\Controllers\Geo\CommuneController;
use App\Http\Controllers\Geo\ArrondissementController;
use App\Http\Controllers\Geo\BureauDeVoteController;
use App\Http\Controllers\Geo\PaysController;
use App\Http\Controllers\Geo\AmbassadeController;
use App\Http\Controllers\VoteDashboardController;
use App\Http\Controllers\ContactController;

use Inertia\Inertia;

// ==================== ROUTES FRONTEND ====================

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/about', [FrontendController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.submit');
Route::get('/latest', [FrontendController::class, 'latest'])->name('latest');
Route::get('/explorer/map', [FrontendController::class, 'map'])->name('map');
Route::get('/services/subcription', [FrontendController::class, 'subcription'])->name('subcription');
Route::get('/services/registered', [FrontendController::class, 'registered'])->name('registered');
Route::post('/inscriptions', [InscriptionController::class, 'store'])->name('inscriptions.store');

// Articles
Route::get('/articles/featured', [ArticleController::class, 'featuredArticles'])->name('articles.featured');
Route::get('/articles/allitems', [ArticleController::class, 'allItemsArticles'])->name('articles.allitems');
Route::get('/articles/all', [ArticleController::class, 'allArticles'])->name('articles.all');
Route::get('/articles/published', [ArticleController::class, 'publishedArticles']);
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');

// Événements
Route::prefix('events')->group(function () {
    Route::get('/allitems', [EventController::class, 'allItemsEvents'])->name('events.allitems');
    Route::get('/all', [EventController::class, 'allEvents'])->name('events.all');
    Route::get('/latest', [EventController::class, 'latest'])->name('events.latest');
});

// Vidéos
Route::prefix('videos')->group(function () {
    Route::get('/latest', [VideoController::class, 'latest'])->name('videos.latest');
    Route::get('/', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/{slug}', [VideoController::class, 'show'])->name('videos.show');
});

// Diaporamas
Route::prefix('slideshows')->group(function () {
    Route::get('/latest', [SlideshowController::class, 'latest'])->name('slideshows.latest');
    Route::get('/', [SlideshowController::class, 'index'])->name('slideshows.index');
    Route::get('/{slug}', [SlideshowController::class, 'show'])->name('slideshows.show');
});

// Catégories
Route::get('/categories/type/{type}', [CategorieController::class, 'getCategoriesByType']);

// ==================== ROUTES BACKEND (AUTH REQUISE) ====================

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('admin/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== MODULE GESTION ÉLECTORALE ====================
    Route::prefix('admin/vote')->name('admin.vote.')->group(function () {
        // Dashboard électoral
        Route::get('/dashboard', [VoteDashboardController::class, 'index'])->name('dashboard');
        
        // Inscriptions
        Route::prefix('inscriptions')->group(function () {
            Route::get('/', [InscriptionController::class, 'adminIndex'])->name('inscriptions.index');
            Route::get('/{inscription}', [InscriptionController::class, 'show'])->name('inscriptions.show');
            Route::patch('/{inscription}/verify', [InscriptionController::class, 'verify'])->name('inscriptions.verify');
            Route::patch('/{inscription}/reject', [InscriptionController::class, 'reject'])->name('inscriptions.reject');
        });
        
        // Statistiques
        Route::prefix('stats')->group(function () {
            Route::get('/', [VoteStatsController::class, 'index'])->name('stats.index');
            Route::get('/regions', [VoteStatsController::class, 'byRegion'])->name('stats.regions');
            Route::get('/bureaux', [VoteStatsController::class, 'byBureau'])->name('stats.bureaux');
            Route::get('/export', [VoteStatsController::class, 'export'])->name('stats.export');
        });
        
        // Résultats
        Route::prefix('results')->group(function () {
            Route::get('/', [VoteResultController::class, 'index'])->name('results.index');
            Route::post('/import', [VoteResultController::class, 'import'])->name('results.import');
            Route::get('/export', [VoteResultController::class, 'export'])->name('results.export');
        });
    });

    // ==================== MODULE GÉOGRAPHIE ÉLECTORALE ====================
    Route::prefix('admin/geo')->group(function () {
        Route::get('/', [VoteDashboardController::class, 'geos'])->name('admin.geo.index');
        // Régions
        Route::prefix('regions')->group(function () {
            Route::get('/', [RegionController::class, 'index'])->name('admin.geo.regions.index');
            Route::get('/create', [RegionController::class, 'create'])->name('admin.geo.regions.create');
            Route::post('/', [RegionController::class, 'store'])->name('admin.geo.regions.store');
            Route::get('/{region}/edit', [RegionController::class, 'edit'])->name('admin.geo.regions.edit');
            Route::put('/{region}', [RegionController::class, 'update'])->name('admin.geo.regions.update');
            Route::delete('/{region}', [RegionController::class, 'destroy'])->name('admin.geo.regions.destroy');
        });

        // Départements
        Route::prefix('departements')->group(function () {
            Route::get('/', [DepartementController::class, 'index'])->name('admin.geo.departements.index');
            Route::get('/create', [DepartementController::class, 'create'])->name('admin.geo.departements.create');
            Route::post('/', [DepartementController::class, 'store'])->name('admin.geo.departements.store');
            Route::get('/{departement}/edit', [DepartementController::class, 'edit'])->name('admin.geo.departements.edit');
            Route::put('/{departement}', [DepartementController::class, 'update'])->name('admin.geo.departements.update');
            Route::delete('/{departement}', [DepartementController::class, 'destroy'])->name('admin.geo.departements.destroy');
        });

        // Communes
        Route::prefix('communes')->group(function () {
            Route::get('/', [CommuneController::class, 'index'])->name('admin.geo.communes.index');
            Route::get('/create', [CommuneController::class, 'create'])->name('admin.geo.communes.create');
            Route::post('/', [CommuneController::class, 'store'])->name('admin.geo.communes.store');
            Route::get('/{commune}/edit', [CommuneController::class, 'edit'])->name('admin.geo.communes.edit');
            Route::put('/{commune}', [CommuneController::class, 'update'])->name('admin.geo.communes.update');
            Route::delete('/{commune}', [CommuneController::class, 'destroy'])->name('admin.geo.communes.destroy');
        });

        // Bureaux de vote
        Route::prefix('bureaux-de-vote')->group(function () {
            Route::get('/', [BureauDeVoteController::class, 'index'])->name('admin.geo.bureaux-de-vote.index');
            Route::get('/create', [BureauDeVoteController::class, 'create'])->name('admin.geo.bureaux-de-vote.create');
            Route::post('/', [BureauDeVoteController::class, 'store'])->name('admin.geo.bureaux-de-vote.store');
            Route::get('/{bureauDeVote}/edit', [BureauDeVoteController::class, 'edit'])->name('admin.geo.bureaux-de-vote.edit');
            Route::get('/{bureauDeVote}/show', [BureauDeVoteController::class, 'show'])->name('admin.geo.bureaux-de-vote.show');
            Route::put('/{bureauDeVote}', [BureauDeVoteController::class, 'update'])->name('admin.geo.bureaux-de-vote.update');
            Route::delete('/{bureauDeVote}', [BureauDeVoteController::class, 'destroy'])->name('admin.geo.bureaux-de-vote.destroy');
        });

        // Diaspora
        Route::prefix('ambassades')->group(function () {
            Route::get('/', [AmbassadeController::class, 'index'])->name('admin.geo.ambassades.index');
            Route::get('/create', [AmbassadeController::class, 'create'])->name('admin.geo.ambassades.create');
            Route::post('/', [AmbassadeController::class, 'store'])->name('admin.geo.ambassades.store');
            Route::get('/{ambassade}/edit', [AmbassadeController::class, 'edit'])->name('admin.geo.ambassades.edit');
            Route::put('/{ambassade}', [AmbassadeController::class, 'update'])->name('admin.geo.ambassades.update');
            Route::delete('/{ambassade}', [AmbassadeController::class, 'destroy'])->name('admin.geo.ambassades.destroy');
        });

        Route::prefix('arrondissements')->group(function () {
            Route::get('/', [ArrondissementController::class, 'index'])->name('admin.geo.arrondissements.index');
            Route::get('/create', [ArrondissementController::class, 'create'])->name('admin.geo.arrondissements.create');
            Route::post('/', [ArrondissementController::class, 'store'])->name('admin.geo.arrondissements.store');
            Route::get('/{arrondissement}/edit', [ArrondissementController::class, 'edit'])->name('admin.geo.arrondissements.edit');
            Route::put('/{arrondissement}', [ArrondissementController::class, 'update'])->name('admin.geo.arrondissements.update');
            Route::delete('/{arrondissement}', [ArrondissementController::class, 'destroy'])->name('admin.geo.arrondissements.destroy');
        });

        Route::resource('pays', PaysController::class)
        ->parameters(['pays' => 'code'])
        ->names('admin.geo.pays');
    });

    // ==================== MODULE MÉDIAS ====================
    Route::get('admin/media', [MediaController::class, 'index'])->name('admin.medias.index');

    // Vidéos
    Route::prefix('admin/media/videos')->name('admin.medias.videos.')->group(function () {
        Route::get('/', [VideoController::class, 'index'])->name('index');
        Route::get('/create', [VideoController::class, 'create'])->name('create');
        Route::post('/store', [VideoController::class, 'store'])->name('store');
        Route::get('/{video}/edit', [VideoController::class, 'edit'])->name('edit');
        Route::put('/{video}', [VideoController::class, 'update'])->name('update');
        Route::patch('/{video}/status', [VideoController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{video}', [VideoController::class, 'destroy'])->name('destroy');
    });

    // Diaporamas
    Route::prefix('admin/media/slideshows')->name('admin.medias.slideshows.')->group(function () {
        Route::get('/', [SlideshowController::class, 'index'])->name('index');
        Route::get('/create', [SlideshowController::class, 'create'])->name('create');
        Route::post('/store', [SlideshowController::class, 'store'])->name('store');
        Route::get('/{slideshow}/edit', [SlideshowController::class, 'edit'])->name('edit');
        Route::put('/{slideshow}', [SlideshowController::class, 'update'])->name('update');
        Route::patch('/{slideshow}/status', [SlideshowController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{slideshow}', [SlideshowController::class, 'destroy'])->name('destroy');
    });

    // ==================== MODULE ARTICLES ====================
    Route::prefix('admin/blogs')->group(function () {
        Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
        Route::get('/create', [ArticleController::class, 'create'])->name('articles.create');
        Route::post('/store', [ArticleController::class, 'store'])->name('articles.store');
        Route::get('/{id}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
        Route::put('/{id}', [ArticleController::class, 'update'])->name('articles.update');
        Route::patch('/{article}/status', [ArticleController::class, 'updateStatus'])->name('admin.blogs.status.update');
        Route::delete('/{id}', [ArticleController::class, 'destroy'])->name('articles.destroy');
    });

    // ==================== MODULE ÉVÉNEMENTS ====================
    Route::prefix('admin/events')->name('admin.events.')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/create', [EventController::class, 'create'])->name('create');
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
        Route::put('/{event}', [EventController::class, 'update'])->name('update');
        Route::patch('/{event}/status', [EventController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
    });

    // ==================== MODULE CATÉGORIES ====================
    Route::prefix('admin/categories')->group(function () {
        Route::get('/', [CategorieController::class, 'index'])->name('categories.index');
        Route::post('/', [CategorieController::class, 'store'])->name('categories.store');
        Route::get('/{id}', [CategorieController::class, 'show'])->name('categories.show');
        Route::put('/{id}', [CategorieController::class, 'update'])->name('categories.update');
        Route::delete('/{id}', [CategorieController::class, 'destroy'])->name('categories.destroy');
    });

    // ==================== MODULE CONTACTS ====================
Route::prefix('admin/contacts')->name('admin.contacts.')->group(function () {
    Route::get('/', [ContactController::class, 'adminIndex'])->name('index');
    Route::get('/export', [ContactController::class, 'export'])->name('export');
    Route::get('/{contact}', [ContactController::class, 'show'])->name('show');
    Route::patch('/{contact}/read', [ContactController::class, 'markAsRead'])->name('read');
    Route::patch('/{contact}/archive', [ContactController::class, 'archive'])->name('archive');
    Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('destroy');
});

    // ==================== MODULE PUBLICATIONS ====================
    Route::prefix('admin/publications')->group(function () {
        Route::get('/', [PublicationController::class, 'index'])->name('publications.index');
        Route::post('/', [PublicationController::class, 'store'])->name('publications.store');
        Route::get('/create', [PublicationController::class, 'create'])->name('publications.create');
        Route::get('/{id}', [PublicationController::class, 'show'])->name('publications.show');
        Route::get('/{id}/edit', [PublicationController::class, 'edit'])->name('publications.edit');
        Route::put('/{id}', [PublicationController::class, 'update'])->name('publications.update');
        Route::delete('/{id}', [PublicationController::class, 'destroy'])->name('publications.destroy');
    });

    // ==================== MODULE GALERIE ====================
    Route::resource('admin/gallery', GalleryController::class);
});