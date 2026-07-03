<?php

namespace App\Http\Middleware;

use Closure;

class CheckRegisterFromApi
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
        if (!config('system_configuration.enable_api_register')) {
            return response()->json([
                'code' => 403,
                'status' => false,
                'message' => 'Access to this route is disabled.'
            ], 403);
        }

        return $next($request);
    }
}
