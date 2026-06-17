<?php

namespace App\Http\Controllers;

use App\Models\ApplicationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = ApplicationUser::where('username', $credentials['username'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $request->session()->put('user_id', $user->id);

            return redirect('/');
        }

        return redirect()->back()->withErrors(['username' => 'Invalid credentials.']);
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|unique:application_users,username',
            'full_name' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()],
        ]);

        ApplicationUser::create([
            'username' => $validatedData['username'],
            'full_name' => $validatedData['full_name'],
            'password' => Hash::make($validatedData['password']),
        ]);

        return redirect('/login')->with('success', 'Registration successful. Please log in.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('user_id');

        return redirect('/login');
    }
}
