<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Competition;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard() {
        $buildCount = Build::count();
        $activeBuildCount = Build::where('active', true)->count();
        $competitionCount = Competition::count();

        return view('admin.dashboard', compact('buildCount', 'activeBuildCount', 'competitionCount'));
    }
}
