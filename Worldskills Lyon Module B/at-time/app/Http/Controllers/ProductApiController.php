<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('company')
            ->where('is_hidden', false)
            ->whereHas('company', function ($companyQuery): void {
                $companyQuery->where('is_deactivated', false);
            });

        if ($request->filled('query')) {
            $keyword = $request->string('query')->toString();

            $query->where(function ($productQuery) use ($keyword): void {
                $productQuery->where('name_en', 'like', '%'.$keyword.'%')
                    ->orWhere('name_fr', 'like', '%'.$keyword.'%')
                    ->orWhere('description_en', 'like', '%'.$keyword.'%')
                    ->orWhere('description_fr', 'like', '%'.$keyword.'%');
            });
        }

        $products = $query->orderBy('id')->paginate(10)->withQueryString();

        return response()->json([
            'data' => $products->getCollection()->map(fn (Product $product): array => $this->transformProduct($product))->values(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'total_pages' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ],
        ]);
    }

    public function show(string $gtin)
    {
        $product = Product::with('company')
            ->where('gtin', $gtin)
            ->where('is_hidden', false)
            ->whereHas('company', function ($companyQuery): void {
                $companyQuery->where('is_deactivated', false);
            })
            ->first();

        if ($product === null) {
            abort(404);
        }

        return response()->json($this->transformProduct($product));
    }

    private function transformProduct(Product $product)
    {
        return [
            'name' => [
                'en' => $product->name_en,
                'fr' => $product->name_fr,
            ],
            'description' => [
                'en' => $product->description_en,
                'fr' => $product->description_fr,
            ],
            'gtin' => $product->gtin,
            'brand' => $product->brand,
            'countryOfOrigin' => $product->country_of_origin,
            'weight' => [
                'gross' => $product->gross_weight,
                'net' => $product->net_weight,
                'unit' => $product->weight_unit,
            ],
            'company' => [
                'companyName' => $product->company->name,
                'companyAddress' => $product->company->address,
                'companyTelephone' => $product->company->telephone,
                'companyEmail' => $product->company->email,
                'owner' => [
                    'name' => $product->company->owner_name,
                    'mobileNumber' => $product->company->owner_mobile,
                    'email' => $product->company->owner_email,
                ],
                'contact' => [
                    'name' => $product->company->contact_name,
                    'mobileNumber' => $product->company->contact_mobile,
                    'email' => $product->company->contact_email,
                ],
            ],
        ];
    }
}
