<?php

namespace App\Http\Controllers;

use App\Models\Design;
use App\Models\NavigationItem;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function navigationItems()
    {
        $navigationItems = NavigationItem::orderBy('order')->get();
        return response()->json($navigationItems);
    }

    public function customizerOptions()
    {
        $designs = Design::where('is_active', true)->get(['id', 'name', 'image_filename']);
        $products = Product::with(['productColors' => function ($query) {
            $query->select(['id', 'product_id', 'name', 'image_filename']);
        }])->get(['id', 'name', 'price']);

        return response()->json([
            'designs' => $designs,
            'products' => $products,
        ]);
    }
}
