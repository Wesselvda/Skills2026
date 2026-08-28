<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin() {
        $navigation = Page::where('show_in_navigation', true)->get();

        return view('login', compact('navigation'));
    }

    public function login(Request $request) {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if (Auth::attempt($validated)) {
            return redirect()->to('/admin');
        }

        return redirect()->back()->withErrors(['password' => 'Invalid credentials']);
    }

    public function logout() {
        Auth::logout();

        return redirect()->to('/login');
    }
}
