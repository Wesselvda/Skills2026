<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApplicationUser;

class isAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check session

        if (!$request->session()->has('user_id')) {
            return redirect('/login');
        }

        $request->attributes->set('user', ApplicationUser::find($request->session()->get('user_id')));

        return $next($request);
    }
}
