<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'passphrase' => ['required', 'string'],
        ]);

        if ($validated['passphrase'] !== 'admin') {
            return back()->withInput()->withErrors([
                'passphrase' => 'Wrong passphrase.',
            ]);
        }

        $request->session()->put('is_admin', true);

        return redirect()->route('companies.index');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('is_admin');

        return redirect()->route('admin.login');
    }
}
