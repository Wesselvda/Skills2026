<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index() {
        $publishedCount = Advert::where('status', 'published')->count();
        $moderationCount = Advert::where('status', 'moderation')->count();
        $declinedCount = Advert::where('status', 'declined')->count();
        $draftCount = Advert::where('status', 'draft')->count();
        $userCount = User::count();
        $topTenAdverts = Advert::where('status', 'published')->orderBy('views_count', 'DESC')->limit(10)->get();

        return view('home', compact('publishedCount', 'moderationCount', 'declinedCount', 'draftCount', 'userCount', 'topTenAdverts'));
    }
}
