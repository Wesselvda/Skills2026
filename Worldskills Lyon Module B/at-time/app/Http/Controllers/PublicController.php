<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function showVerification()
    {
        return view('public.verify', [
            'results' => [],
            'allValid' => false,
            'input' => '',
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'gtins' => ['required', 'string'],
        ]);

        $input = trim($validated['gtins']);
        $normalizedInput = str_replace(["\r\n", "\r"], "\n", $input);
        $lines = explode("\n", $normalizedInput);

        $results = [];
        $allValid = count($lines) > 0;

        foreach ($lines as $line) {
            $gtin = trim($line);

            if ($gtin === '') {
                continue;
            }

            $valid = Product::where('gtin', $gtin)
                ->where('is_hidden', false)
                ->whereHas('company', function ($companyQuery): void {
                    $companyQuery->where('is_deactivated', false);
                })
                ->exists();

            $results[] = [
                'gtin' => $gtin,
                'valid' => $valid,
            ];

            if (! $valid) {
                $allValid = false;
            }
        }

        return view('public.verify', [
            'results' => $results,
            'allValid' => $allValid && count($results) > 0,
            'input' => $input,
        ]);
    }

    public function product(Request $request, string $gtin)
    {
        $language = $request->string('lang')->toString() === 'fr' ? 'fr' : 'en';

        $product = Product::with('company')
            ->where('gtin', $gtin)
            ->where('is_hidden', false)
            ->whereHas('company', function ($companyQuery): void {
                $companyQuery->where('is_deactivated', false);
            })
            ->firstOrFail();

        return view('public.product', [
            'product' => $product,
            'language' => $language,
        ]);
    }
}
