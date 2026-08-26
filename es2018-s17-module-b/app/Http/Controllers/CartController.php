<?php

namespace App\Http\Controllers;

use App\Models\Design;
use App\Models\Order;
use App\Models\OrderItem;
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

    public function viewCheckout()
    {
        return view('checkout');
    }

    public function placeOrder(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $cartItems = session('cart', []);

        if (empty($cartItems)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $order = new Order();
        $order->first_name = $validatedData['first_name'];
        $order->last_name = $validatedData['last_name'];
        $order->email = $validatedData['email'];
        $order->save();

        foreach ($cartItems as $item) {
            $design = Design::find($item['design']);
            $color = ProductColor::with('product')->find($item['color']);

            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_color_id = $color->id;
            $orderItem->design_id = $design->id;
            $orderItem->price = $color->product->price;
            $orderItem->save();
        }

        session()->forget('cart');

        return redirect()->route('home')->with('success', 'Order placed successfully!');
    }
}
