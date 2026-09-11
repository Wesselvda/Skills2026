<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin() {
        return view('auth.login');
    }

    public function login(Request $request) {
        $validated = $request->validate([
            'phoneoremail' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('email', $validated['phoneoremail'])->first();

        if (!$user) {
            $user = User::where('phone', $validated['phoneoremail'])->first();
        }

        if ($user && $user->role === "moderator") {
            if (password_verify($validated['password'], $user->password)) {
                Auth::login($user);

                return redirect('/')->with('success', "Logged in successfully");
            }
        }

        return redirect()->back()->withErrors(['password' => "Invalid credentials"]);
    }

    public function logout() {
        Auth::logout();

        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
