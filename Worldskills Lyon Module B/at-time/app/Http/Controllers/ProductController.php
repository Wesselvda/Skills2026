<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('company')
            ->orderBy('gtin')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'companies' => Company::where('is_deactivated', false)->orderBy('name')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['image_path'] = $this->storeImageIfUploaded($request);
        $product = Product::create($data);

        return redirect()->route('products.show', $product)->with('status', 'Product created.');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', [
            'product' => $product->load('company'),
            'companies' => Company::where('is_deactivated', false)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validatedData($request, $product->id);
        $newImagePath = $this->storeImageIfUploaded($request);

        if ($newImagePath !== null) {
            if ($product->image_path !== null && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $newImagePath;
        }

        $product->update($data);

        return redirect()->route('products.show', $product)->with('status', 'Product updated.');
    }

    public function hide(Product $product)
    {
        $product->update(['is_hidden' => true]);

        return redirect()->route('products.show', $product)->with('status', 'Product hidden.');
    }

    public function destroy(Product $product)
    {
        if (! $product->is_hidden) {
            return back()->with('status', 'Only hidden products can be deleted.');
        }

        if ($product->image_path !== null && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')->with('status', 'Hidden product deleted.');
    }

    public function removeImage(Product $product)
    {
        if ($product->image_path !== null && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->update(['image_path' => null]);

        return redirect()->route('products.show', $product)->with('status', 'Product image removed.');
    }

    private function validatedData(Request $request, ?int $productId = null)
    {
        return $request->validate([
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')->where('is_deactivated', false)],
            'gtin' => ['required', 'digits_between:13,14', Rule::unique('products', 'gtin')->ignore($productId)],
            'name_en' => ['required', 'string', 'max:255'],
            'name_fr' => ['required', 'string', 'max:255'],
            'description_en' => ['required', 'string'],
            'description_fr' => ['required', 'string'],
            'brand' => ['required', 'string', 'max:255'],
            'country_of_origin' => ['required', 'string', 'max:255'],
            'gross_weight' => ['required', 'numeric', 'min:0'],
            'net_weight' => ['required', 'numeric', 'min:0'],
            'weight_unit' => ['required', 'string', 'max:10'],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_hidden' => ['nullable', 'boolean'],
        ]);
    }

    private function storeImageIfUploaded(Request $request)
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('product_images', 'public');
    }
}
