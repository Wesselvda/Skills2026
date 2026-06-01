<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiToken = $request->header('X-API-TOKEN');

        if (!$apiToken) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $token = ApiToken::where('token', $apiToken)->whereNull('revoked_at')->with('user')->firstOrFail();
        
        Auth::setUser($token->user);

        return $next($request);
    }
}
