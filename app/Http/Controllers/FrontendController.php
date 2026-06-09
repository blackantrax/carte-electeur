<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class FrontendController extends Controller
{
    public function index()
    {
        return Inertia::render('Home');
    }

    public function about()
    {
        return Inertia::render('About', [
            'title' => 'About Us',
            'description' => 'This is the about page content.'
        ]);
    }

    public function latest()
    {
        return Inertia::render('Latest', [
            'title' => 'Dernières Nouvelles',
            'description' => 'This is the latest news page content.'
        ]);
    }

    public function map()
    {
        return Inertia::render('MapViewComponent', [
            'title' => 'Our Location',
            'description' => 'This is the map page content.'
        ]);
    }

    public function subcription()
    {
        return Inertia::render('Services/Subscription', [
            'title' => 'Subscribe Now',
            'description' => 'This is the subscription page content.'
        ]);
    }

    public function registered()
    {
        return Inertia::render('Services/Registered', [
            'title' => 'Registered Users',
            'description' => 'This is the registered users page content.'
        ]);
    }
}
