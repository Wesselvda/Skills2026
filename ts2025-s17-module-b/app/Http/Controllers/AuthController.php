<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email_or_phone' => ['required'],
            'password' => ['required'],
        ]);

        $fieldType = filter_var($credentials['email_or_phone'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [$fieldType => $credentials['email_or_phone'], 'password' => $credentials['password']];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('categories.index');
        }

        return back()->withErrors([
            'email_or_phone' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'));
    }
}
