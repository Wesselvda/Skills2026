<?php

namespace App\Http\Controllers;

use App\Models\Bicycle;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BicycleController extends Controller
{
    public function index(Category $category)
    {
        $this->checkCategoryOwner($category);

        $bicycles = $category->bicycles()
            ->orderBy('name')
            ->get();

        return view('bicycles.index', compact('category', 'bicycles'));
    }

    public function create(Category $category)
    {
        $this->checkCategoryOwner($category);

        return view('bicycles.create', compact('category'));
    }

    public function store(Request $request, Category $category)
    {
        $this->checkCategoryOwner($category);

        $request->validate([
            'name' => ['required', 'max:100'],
            'description' => ['nullable'],
            'wear' => ['nullable', 'integer', 'min:0', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'image' => ['nullable', 'image'],
        ]);

        $imageName = $this->uploadImage($request);

        for ($i = 0; $i < $request->quantity; $i++) {
            Bicycle::create([
                'bicycle_key' => $this->newBicycleKey(),
                'name' => $request->name,
                'slug' => $this->newSlug($request->name),
                'description' => $request->description ?? '',
                'wear' => $request->wear ?? 0,
                'status' => 'available',
                'locationX' => $this->randomCoordinate(),
                'locationY' => $this->randomCoordinate(),
                'pathToImage' => $imageName ?? '',
                'category_ref' => $category->category_key,
            ]);
        }

        return redirect()->route('bicycles.index', $category)->with('success', 'Bicycle created.');
    }

    public function edit(Bicycle $bicycle)
    {
        $this->checkBicycleOwner($bicycle);

        return view('bicycles.edit', compact('bicycle'));
    }

    public function update(Request $request, Bicycle $bicycle)
    {
        $this->checkBicycleOwner($bicycle);

        $request->validate([
            'name' => ['required', 'max:100'],
            'description' => ['nullable'],
            'wear' => ['required', 'integer', 'min:0', 'max:100'],
            'image' => ['nullable', 'image'],
        ]);

        $imageName = $bicycle->pathToImage;
        $newImageName = $this->uploadImage($request);

        if ($newImageName) {
            $imageName = $newImageName;
        }

        $bicycle->update([
            'name' => $request->name,
            'description' => $request->description ?? '',
            'wear' => $request->wear,
            'pathToImage' => $imageName,
        ]);

        return redirect()->route('bicycles.index', $bicycle->category)->with('success', 'Bicycle updated.');
    }

    public function status(Request $request, Bicycle $bicycle)
    {
        $this->checkBicycleOwner($bicycle);

        $request->validate([
            'status' => ['required', 'in:available,unavailable'],
        ]);

        if ($request->status === 'unavailable' && $bicycle->bookings()->whereNull('endedAt')->count() > 0) {
            return back()->withErrors([
                'bicycle' => 'This bicycle is rented, so it cannot be set to unavailable.',
            ]);
        }

        $bicycle->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(Bicycle $bicycle)
    {
        $this->checkBicycleOwner($bicycle);

        if ($bicycle->status !== 'unavailable') {
            return back()->withErrors([
                'bicycle' => 'Only unavailable bicycles can be deleted.',
            ]);
        }

        $category = $bicycle->category;
        $bicycle->delete();

        return redirect()->route('bicycles.index', $category)->with('success', 'Bicycle deleted.');
    }

    private function checkCategoryOwner(Category $category)
    {
        if ($category->user_ref !== Auth::user()->user_key) {
            abort(403);
        }
    }

    private function checkBicycleOwner(Bicycle $bicycle)
    {
        $this->checkCategoryOwner($bicycle->category);
    }

    private function newBicycleKey()
    {
        $number = Bicycle::count() + 1;

        while (true) {
            $key = 'BIC_' . str_pad($number, 2, '0', STR_PAD_LEFT);

            if (! Bicycle::where('bicycle_key', $key)->exists()) {
                return $key;
            }

            $number++;
        }
    }

    private function newSlug(string $name)
    {
        $slug = Str::slug($name);

        if (! Bicycle::where('slug', $slug)->exists()) {
            return $slug;
        }

        $number = 1;

        while (true) {
            $newSlug = $slug . '-' . str_pad($number, 2, '0', STR_PAD_LEFT);

            if (! Bicycle::where('slug', $newSlug)->exists()) {
                return $newSlug;
            }

            $number++;
        }
    }

    private function randomCoordinate()
    {
        return random_int(10, 4990);
    }

    private function uploadImage(Request $request)
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $name = time() . '-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->extension();
        $file->storeAs('bicycle-images', $name, 'public');

        return $name;
    }
}
