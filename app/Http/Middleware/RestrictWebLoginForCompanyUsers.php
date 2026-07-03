<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RestrictWebLoginForCompanyUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->companies()->exists() && !$user->company) {
            Auth::logout();
            abort(404, 'Página no encontrada');
        }

        return $next($request);
    }
}
