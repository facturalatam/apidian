<?php

namespace App\Http\Middleware;

use App\Company;
use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureCompanyWebAccess
{
    /**
     * Handle an incoming request.
     *
     * Si la ruta no tiene parámetro {company}, no hace nada.
     * Si lo tiene, valida que el usuario autenticado tenga acceso a esa empresa.
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $rawCompany = $request->route('company');
        if ($rawCompany === null) {
            return $next($request);
        }

        $company = null;
        if ($rawCompany instanceof Company) {
            $company = $rawCompany;
        } else {
            if (is_numeric($rawCompany)) {
                $company = Company::find($rawCompany);
            } else {
                $company = Company::where('identification_number', $rawCompany)->first();
            }
        }

        // Si no se pudo resolver, dejamos que el controlador maneje el 404.
        if (!$company) {
            return $next($request);
        }

        $user = Auth::user();
        if ($user && method_exists($user, 'canAccessCompany') && $user->canAccessCompany($company)) {
            return $next($request);
        }

        abort(403, 'No tienes acceso a esta empresa.');
    }
}
