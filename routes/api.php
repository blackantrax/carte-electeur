<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategorieController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ici, nous définissons les routes pour notre API. Ces routes sont
| chargées par le RouteServiceProvider et auront le préfixe "api".
|
*/

// Route pour récupérer l'utilisateur authentifié
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes API pour la gestion des catégories
Route::apiResource('categories', CategorieController::class);
