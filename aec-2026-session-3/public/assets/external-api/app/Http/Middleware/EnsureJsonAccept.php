<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureJsonAccept
{
    public function handle(Request $request, Closure $next)
    {
        // Only set if missing
        if (!$request->headers->has('Accept')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
