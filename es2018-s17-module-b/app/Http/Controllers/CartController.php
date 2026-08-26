<?php

namespace App\Http\Controllers;

use App\Models\Design;
use App\Models\ProductColor;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $validatedData = $request->validate([
            'design' => 'required|integer|exists:designs,id',
            'color' => 'required|integer|exists:product_colors,id',
        ]);

        if (!session()->has('cart')) {
            session(['cart' => []]);
        }

        session()->push('cart', $validatedData);

        return redirect()->back()->with('success', 'Item added to cart successfully!');
    }
    
    public function viewCart()
    {
        $cartItems = session('cart', []);

        $cartDetails = collect($cartItems)->map(function ($item) {
            $design = Design::find($item['design']);
            $color = ProductColor::with('product')->find($item['color']);

            return [
                'design' => $design,
                'color' => $color,
            ];
        });

        $totalPrice = $cartDetails->sum(function ($item) {
            return $item['color']->product->price;
        });

        return view('cart', compact('cartDetails', 'totalPrice'));
    }
}
