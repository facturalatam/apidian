<?php

namespace App\Http\Middleware;

use Closure;

class ApiTokenConfig
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
        $token = $request->bearerToken();

        if (config('app.use_protection_api_key') === false) {
            return $next($request);
        }

        if (is_null($token) || $token !== config('app.api_key')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
