<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Video;
use App\Models\Slideshow;


class MediaController extends Controller
{
    // app/Http/Controllers/MediaController.php
    public function index()
    {
        $videos = Video::with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        $slideshows = Slideshow::with('category') // Si vous avez des diaporamas
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dashboard/medias/index', [
            'videos' => $videos,
            'slideshows' => $slideshows,
            'initialTab' => request()->query('tab', 'videos') // Pour conserver l'onglet actif
        ]);
    }
}
