<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],  // Autorise les requêtes API et le cookie CSRF de Sanctum

    'allowed_methods' => ['*'],  // Autorise toutes les méthodes (GET, POST, PUT, DELETE, etc.)

    'allowed_origins' => ['http://127.0.0.1:8000', 'http://localhost:5173'],  // Autorise toutes les origines (⚠️ En production, remplacer par l'URL du frontend)

    'allowed_origins_patterns' => [],  // Laisse vide si `allowed_origins` est bien configuré

    'allowed_headers' => ['*'],  // Autorise tous les headers

    'exposed_headers' => [],  // Headers accessibles par le frontend (laisser vide si inutile)

    'max_age' => 0,  // Durée en secondes pendant laquelle une requête préflight (OPTIONS) est mise en cache

    'supports_credentials' => true,  // ⚠️ Important pour envoyer les cookies d’authentification (Sanctum)

];
