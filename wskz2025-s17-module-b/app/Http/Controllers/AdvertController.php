<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Advert;
use App\Models\PaidService;
use Illuminate\Http\Request;

class AdvertController extends Controller
{
    // Categories

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


    // Adverts

    public function showAdverts(Request $request) {
        $adverts = $this->filteredAdvertsQuery($request)
            ->with(['category', 'author', 'paidServices'])
            ->get();

        $categories = Category::all();

        return view('adverts', compact('adverts', 'categories'));
    }

    public function showAdvertDetail(Advert $advert) {
        $advert->load(['category', 'author', 'paidServices']);

        return view('advert_detail', compact('advert'));
    }

    public function updateStatus(Request $request, Advert $advert) {
        $validated = $request->validate([
            'status' => ['required', 'in:published,declined'],
        ]);

        $allowedTransitions = [
            'moderation' => ['published', 'declined'],
            'published' => ['declined'],
        ];

        if (! in_array($validated['status'], $allowedTransitions[$advert->status] ?? [], true)) {
            return back()->with('error', "The advert cannot move from {$advert->status} to {$validated['status']}.");
        }

        $advert->update(['status' => $validated['status']]);

        return back()->with('success', 'Advert status updated successfully.');
    }

    public function togglePaidService(Advert $advert, PaidService $paidService) {
        abort_unless($paidService->advert_id === $advert->id, 404);

        $paidService->update(['is_enabled' => ! $paidService->is_enabled]);

        return back()->with('success', "{$paidService->type} service " . ($paidService->is_enabled ? 'enabled.' : 'disabled.'));
    }

    public function exportAdverts(Request $request) {
        $adverts = $this->filteredAdvertsQuery($request)
            ->with(['category', 'author'])
            ->get();

        $csvHeader = ['ID', 'Title', 'Category Name', 'Price', 'Author Phone number', 'Author Email', 'Text', 'Publication Date'];
        $csvData = [];

        foreach ($adverts as $advert) {
            $csvData[] = [
                $advert->id,
                $advert->title,
                $advert->category->name ?? '',
                $advert->price,
                $advert->author->phone ?? '',
                $advert->author->email ?? '',
                $advert->text,
                $advert->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $filepath = storage_path("export_adverts.csv");

        $file = fopen($filepath, 'w');
        fputcsv($file, $csvHeader);

        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    private function filteredAdvertsQuery(Request $request)
    {
        $query = Advert::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->filled('search')) {
            $searchTerm = strtolower($request->input('search'));

            $query->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(title) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(text) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                        $categoryQuery->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%']);
                    })
                    ->orWhereHas('author', function ($authorQuery) use ($searchTerm) {
                        $authorQuery->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%']);
                    });
            });
        }

        return $query;
    }
}
