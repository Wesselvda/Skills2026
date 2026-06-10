<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TariffController extends Controller
{
    public function index(Category $category)
    {
        $this->checkCategoryOwner($category);

        $tariffs = $category->tariffs()
            ->orderBy('archived')
            ->orderBy('name')
            ->get();

        return view('tariffs.index', compact('category', 'tariffs'));
    }

    public function create(Category $category)
    {
        $this->checkCategoryOwner($category);

        return view('tariffs.create', compact('category'));
    }

    public function store(Request $request, Category $category)
    {
        $this->checkCategoryOwner($category);
        $this->validateTariff($request);

        if ($this->duplicateTariff($category, $request->name, $request->type)) {
            return back()->withInput()->withErrors([
                'name' => 'This tariff already exists for this category.',
            ]);
        }

        Tariff::create([
            'tariff_key' => $this->newTariffKey(),
            'name' => $request->name,
            'type' => $request->type,
            'basePrice' => $request->basePrice,
            'minPrice' => $request->type === 'DYNAMIC' ? $request->minPrice : null,
            'maxPrice' => $request->type === 'DYNAMIC' ? $request->maxPrice : null,
            'category_ref' => $category->category_key,
            'archived' => false,
        ]);

        return redirect()->route('tariffs.index', $category)->with('success', 'Tariff created.');
    }

    public function edit(Tariff $tariff)
    {
        $this->checkTariffOwner($tariff);

        return view('tariffs.edit', compact('tariff'));
    }

    public function update(Request $request, Tariff $tariff)
    {
        $this->checkTariffOwner($tariff);
        $this->validateTariff($request);

        $duplicate = Tariff::where('category_ref', $tariff->category_ref)
            ->where('name', $request->name)
            ->where('type', $request->type)
            ->where('id', '!=', $tariff->id)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'name' => 'This tariff already exists for this category.',
            ]);
        }

        $tariff->update([
            'name' => $request->name,
            'type' => $request->type,
            'basePrice' => $request->basePrice,
            'minPrice' => $request->type === 'DYNAMIC' ? $request->minPrice : null,
            'maxPrice' => $request->type === 'DYNAMIC' ? $request->maxPrice : null,
        ]);

        return redirect()->route('tariffs.index', $tariff->category)->with('success', 'Tariff updated.');
    }

    public function archive(Tariff $tariff)
    {
        $this->checkTariffOwner($tariff);

        $tariff->update([
            'archived' => true,
        ]);

        return back()->with('success', 'Tariff archived.');
    }

    private function validateTariff(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:100'],
            'type' => ['required', 'in:STATIC,DYNAMIC'],
            'basePrice' => ['required', 'integer', 'min:0'],
            'minPrice' => ['nullable', 'integer', 'min:0'],
            'maxPrice' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->type === 'DYNAMIC') {
            $request->validate([
                'minPrice' => ['required', 'integer', 'min:0'],
                'maxPrice' => ['required', 'integer', 'min:' . $request->minPrice],
            ]);
        }
    }

    private function duplicateTariff(Category $category, string $name, string $type)
    {
        return Tariff::where('category_ref', $category->category_key)
            ->where('name', $name)
            ->where('type', $type)
            ->exists();
    }

    private function checkCategoryOwner(Category $category)
    {
        if ($category->user_ref !== Auth::user()->user_key) {
            abort(403);
        }
    }

    private function checkTariffOwner(Tariff $tariff)
    {
        $this->checkCategoryOwner($tariff->category);
    }

    private function newTariffKey()
    {
        $number = Tariff::count() + 1;

        while (true) {
            $key = 'TAR_' . str_pad($number, 2, '0', STR_PAD_LEFT);

            if (! Tariff::where('tariff_key', $key)->exists()) {
                return $key;
            }

            $number++;
        }
    }
}
