<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\StorageService;

class SetCompanyStorageContext
{
    /**
     * Configura el contexto de almacenamiento de la empresa autenticada.
     * Se ejecuta automáticamente en todas las rutas auth:api, sin necesidad
     * de llamar StorageService::setCompany() en cada controlador.
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if ($user && $user->company) {
            StorageService::setCompany($user->company);
        }

        try {
            return $next($request);
        } finally {
            StorageService::clearCompany();
        }
    }
}
