<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('user_ref', Auth::user()->user_key)
            ->withCount('bicycles')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:100'],
        ]);

        Category::create([
            'category_key' => $this->newCategoryKey(),
            'name' => $request->name,
            'user_ref' => Auth::user()->user_key,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        $this->checkOwner($category);

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->checkOwner($category);

        $request->validate([
            'name' => ['required', 'max:100'],
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $this->checkOwner($category);

        if ($category->bicycles()->count() > 0) {
            return back()->withErrors([
                'category' => 'This category still has bicycles.',
            ]);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }

    private function checkOwner(Category $category)
    {
        if ($category->user_ref !== Auth::user()->user_key) {
            abort(403);
        }
    }

    private function newCategoryKey()
    {
        $number = Category::count() + 1;

        while (true) {
            $key = 'CAT_' . str_pad($number, 2, '0', STR_PAD_LEFT);

            if (! Category::where('category_key', $key)->exists()) {
                return $key;
            }

            $number++;
        }
    }
}
