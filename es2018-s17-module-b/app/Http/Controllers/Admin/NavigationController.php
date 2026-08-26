<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function index()
    {
        $navigationItems = NavigationItem::orderBy('order')->get();

        return view('admin.main-nav', compact('navigationItems'));
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'exists:navigation_items,id'],
        ]);

        foreach ($validated['order'] as $position => $id) {
            NavigationItem::where('id', $id)->update(['order' => $position + 1]);
        }

        return redirect()->route('admin.main-nav')->with('success', 'Navigation order saved.');
    }
}
