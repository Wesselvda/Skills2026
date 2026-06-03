<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function index()
    {
        $tours = Tour::with('bookings')->get();

        return view('tours.index', compact('tours'));
    }

    public function show(Tour $tour)
    {
        $tour->load('bookings');

        return view('tours.show', compact('tour'));
    }
}
