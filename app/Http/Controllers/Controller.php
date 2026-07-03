<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function getCompanyId()
    {
        $company = auth()->user()->company; // Relación directa
        $companies = auth()->user()->companies; // Relación many-to-many

        if ($company) {
            // Caso 1: Usuario con relación directa a `company`
            return $company;
        } elseif ($companies->isNotEmpty()) {
            // Caso 2: Usuario con relación many-to-many a `companies`
            return $companies->first();
        }
    }
}
