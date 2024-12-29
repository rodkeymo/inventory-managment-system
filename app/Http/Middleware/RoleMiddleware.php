<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if the user is authenticated and has a valid role
        if (Auth::check() && in_array(Auth::user()->role->name, $roles)) {
            return $next($request);
        }

        // Redirect unauthorized users
        return redirect()->route('products.index')->with('error', 'Unauthorized access.');
    }
}
