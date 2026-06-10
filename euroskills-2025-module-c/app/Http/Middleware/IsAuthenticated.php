<?php

namespace App\Http\Middleware;

use App\Models\ApplicationUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');

        if ($token) {
            if(str_starts_with($token, "Bearer ")) {
                $token = trim(str_replace("Bearer ", "", $token));

                $user = ApplicationUser::where('token', $token)->first();

                if ($user) {
                    $request->attributes->set('user', $user);

                    return $next($request);
                }
            }
        }

        Log::debug("Failed");

        return response()->json(
            [
                "type" => "http://localhost:8080/problemtype/unauthorized",
                "title" => "Not authenticated",
                "status" => 599,
                "detail" => "The user is not authenticated",
                "instance" => "No idea",
                "errors" => [
                    "message" => "Not authenticated"
                ]
            ],
            401,
            [
                "Content-Type" => "application/problem+json"
            ]
        );
    }
}
