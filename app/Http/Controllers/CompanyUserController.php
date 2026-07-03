<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;
use App\User;

class CompanyUserController extends Controller
{
    protected function ensureCanManageCompany(Company $company): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        if ($user->isPlatformAdmin()) {
            return;
        }

        if ($user->isCompanyOwner($company)) {
            return;
        }

        abort(403, 'No tienes permiso para administrar esta empresa.');
    }

    public function emailIndex($companyId)
    {
        $company = Company::with('user')->findOrFail($companyId);
        $this->ensureCanManageCompany($company);
        $user = $company->user;

        // Obtener configuracion actual
        $emailConfig = [
            'mail_host' => $user->mail_host ?: config('mail.host'),
            'mail_port' => $user->mail_port ?: config('mail.port'),
            'mail_username' => $user->mail_username ?: config('mail.username'),
            'mail_password' => $user->mail_password ? '********' : '',
            'mail_encryption' => $user->mail_encryption ?: config('mail.encryption'),
            'mail_from_address' => $user->mail_from_address ?: config('mail.from.address'),
            'mail_from_name' => $user->mail_from_name ?: config('mail.from.name'),
            'has_custom_config' => !empty($user->mail_host)
        ];

        return view('company.email', compact('company', 'emailConfig'));
    }

    public function emailStore(Request $request, $companyId)
    {
        $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'required|string|in:tls,ssl',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',
        ]);

        try {
            $company = Company::findOrFail($companyId);
            $this->ensureCanManageCompany($company);
            $user = $company->user;

            $user->update([
                'mail_host' => $request->mail_host,
                'mail_port' => $request->mail_port,
                'mail_username' => $request->mail_username,
                'mail_password' => $request->mail_password,
                'mail_encryption' => $request->mail_encryption,
                'mail_from_address' => $request->mail_from_address,
                'mail_from_name' => $request->mail_from_name,
            ]);

            return redirect()->back()->with('success', 'Configuracion de correo actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrio un error al actualizar la configuracion de correo.');
        }
    }
}
