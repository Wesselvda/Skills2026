<?php

namespace App\Http\Controllers;

use App\Models\ApplicationUser;
use App\Models\MockEmail;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function handleLogin(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $generatedLoginCode = bin2hex(random_bytes(16));

        ApplicationUser::updateOrCreate(
            ['email' => $validated['email']],
            [
                'email' => $validated['email'],
                'login_code' => $generatedLoginCode,
            ]
        );

        MockEmail::create([
            'recipient' => $validated['email'],
            'subject' => 'Your Login Code',
            'body' => view('emails.login-code', ['loginCode' => $generatedLoginCode, 'email' => $validated['email']])->render(),
        ]);

        return view('auth.email-sent');
    }

    public function handleLoginCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'login_code' => 'required|string',
        ]);

        $user = ApplicationUser::where('email', $validated['email'])
            ->where('login_code', $validated['login_code'])
            ->first();

        if ($user) {
            $request->session()->put('user_id', $user->id);
            return redirect('/');
        }

        return back()->withErrors(['Invalid login code.']);
    }
}
