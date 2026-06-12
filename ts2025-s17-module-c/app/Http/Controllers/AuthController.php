<?php

namespace App\Http\Controllers;

use App\Models\ApplicationUser;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends ApiController
{
    private $clientId = 'cid-3f7b64fd';

    private $clientSecret = 'csec-b9c0064b';

    public function signIn(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = ApplicationUser::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->fail('Invalid email or password', 401, 'Unauthorized');
        }

        return $this->data(['accessToken' => $this->issueToken($user)]);
    }

    public function signUp(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if (ApplicationUser::where('email', $validated['email'])->exists()) {
            return $this->fail('Invalid email', 401, 'Unauthorized');
        }

        $user = ApplicationUser::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'balance' => 0,
            'password' => Hash::make($validated['password']),
        ]);

        return $this->data(['accessToken' => $this->issueToken($user)], 201);
    }

    public function signOut(Request $request)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->fail('Missing token', 401, 'Unauthorized');
        }

        if (UserToken::where('hash', hash('sha256', $token))->delete() === 0) {
            return $this->fail('Invalid token', 401, 'Unauthorized');
        }

        return $this->empty();
    }

    public function getOAuthLink(Request $request)
    {
        if ($request->has('code')) {
            return $this->loginOAuth($request);
        }

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => url('/api/v1/auth/oauth'),
            'scope' => 'openid',
        ]);

        return $this->data(['link' => 'http://localhost:7000/authorize?'.$query]);
    }

    public function loginOAuth(Request $request)
    {
        $code = $request->query('code');

        if (! is_string($code) || $code === '') {
            return $this->fail('Bad Request', 400);
        }

        try {
            $tokenResponse = Http::asForm()->timeout(5)->post('http://localhost:7000/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'authorization_code',
                'code' => $code,
            ])->throw()->json();

            $oauthUser = Http::timeout(5)
                ->withToken($tokenResponse['access_token'])
                ->get('http://localhost:7000/userinfo')
                ->throw()
                ->json();
        } catch (\Throwable) {
            return $this->fail('Service Unavailable', 503);
        }

        $email = $oauthUser['email'] ?? null;

        if (! is_string($email)) {
            return $this->fail('Unprocessable Entity', 422);
        }

        $user = ApplicationUser::firstOrCreate(
            ['email' => $email],
            [
                'id' => (string) Str::uuid(),
                'name' => $oauthUser['name'] ?? $email,
                'phone' => $oauthUser['phone'] ?? '',
                'balance' => 0,
                'password' => Hash::make(Str::random(32)),
            ],
        );

        return $this->data(['accessToken' => $this->issueToken($user)]);
    }

    private function issueToken(ApplicationUser $user)
    {
        $token = Str::random(80);

        UserToken::create([
            'id' => (string) Str::uuid(),
            'value' => $token,
            'hash' => hash('sha256', $token),
            'user_id' => $user->id,
        ]);

        return $token;
    }
}
