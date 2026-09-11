<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Advert;
use Illuminate\Http\Request;

class AdvertController extends Controller
{
    public function showCategories() {
        $categories = Category::all();

        return view('categories', compact('categories'));
    }

    public function editCategory($id) {
        $category = Category::findOrFail($id);

        return view('edit_category', compact('category'));
    }

    public function updateCategory(Request $request, $id) {
        $category = Category::findOrFail($id);
        $category->update($request->only('name'));

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroyCategory($id) {
        $category = Category::findOrFail($id);

        if ($category->adverts()->count() > 0) {
            return redirect()->route('categories.index')->withErrors(['error' => 'Cannot delete category with associated adverts.']);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function showAddCategory() {
        return view('add_category');
    }

    public function addCategory(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $highestCategoryNumber = Category::query()
            ->pluck('id')
            ->map(function (string $id) {
                return (int) substr($id, 1);
            })
            ->max() ?? 0;

        $newId = 'C' . ($highestCategoryNumber + 1);

        Category::create([
            'id' => $newId,
            'name' => $request->input('name'),
        ]);

        return redirect()->route('categories.index')->with('success', 'Category added successfully.');
    }
}
