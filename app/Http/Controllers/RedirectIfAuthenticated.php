<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // $guard = 'web';
        if (Auth::guard($guard)->check()) {
            return redirect('/home');
        }
        // die("Estamos en el handle, guard: -{$guard}-");
        dd($request);
        return $next($request);
    }
}
