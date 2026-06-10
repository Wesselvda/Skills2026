<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function index()
    {
        $categoryKeys = Category::where('user_ref', Auth::user()->user_key)
            ->pluck('category_key');

        $applications = Application::with(['category', 'user'])
            ->whereIn('category_ref', $categoryKeys)
            ->orderBy('status')
            ->orderByDesc('created_at')
            ->get();

        return view('applications.index', compact('applications'));
    }

    public function approve(Application $application)
    {
        $this->checkOwner($application);

        $application->update([
            'status' => 'approved',
        ]);

        return back()->with('success', 'Application approved.');
    }

    public function reject(Application $application)
    {
        $this->checkOwner($application);

        $application->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Application rejected.');
    }

    private function checkOwner(Application $application)
    {
        if ($application->category->user_ref !== Auth::user()->user_key) {
            abort(403);
        }
    }
}
