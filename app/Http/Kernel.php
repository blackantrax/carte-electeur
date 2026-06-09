<?php

namespace App\Http;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareGroups = [
        'api' => [
            \App\Http\Middleware\HandleCors::class,
            EnsureFrontendRequestsAreStateful::class, // <-- Important pour Jetstream & Inertia.js
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];
}
