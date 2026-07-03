<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Company;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'id_administrator',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'api_token',
        'can_health',
        'status',
        'code_service_provider',
        'document_number',
        'document_type_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'api_token', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'boolean',
        'can_health' => 'boolean',
    ];

    /**
     * Get the company record associated with the user.
     */
    public function company()
    {
        return $this->hasOne(Company::class);
    }

    /**
     * Get the companies associated with the user.
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user');
    }

    public function isPlatformAdmin(): bool
    {
        return !$this->company()->exists() && !$this->companies()->exists();
    }

    public function canAccessCompany(Company $company): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        if ((int) $company->user_id === (int) $this->id) {
            return true;
        }

        return $company->users()->where('users.id', $this->id)->exists();
    }

    public function isCompanyOwner(Company $company): bool
    {
        return (int) $company->user_id === (int) $this->id;
    }

    public function validate_mail_server()
    {
        if(!is_null($this->mail_host) && !is_null($this->mail_port) && !is_null($this->mail_username) && !is_null($this->mail_password) && !is_null($this->mail_encryption))
          if($this->mail_host != '' && $this->mail_port != '' && $this->mail_username != '' && $this->mail_password != '' && $this->mail_encryption != '')
            return true;
          else
            return false;
        else
          return false;
    }

    /**
     * Aplica la configuracion de correo de la empresa al envio actual.
     *
     * Prioridad del servidor SMTP:
     *   1) smtp_parameters recibidos en el request
     *   2) la configuracion guardada de la empresa (mail_*)
     *   3) la configuracion global del .env (si no hay ninguna)
     *
     * Prioridad del remitente (From):
     *   1) el configurado por la empresa/request (from_address / from_name)
     *   2) el global del .env (MAIL_FROM_ADDRESS / MAIL_FROM_NAME) - comportamiento previo
     *   3) el usuario SMTP autenticado, solo como red de seguridad si todo quedo vacio
     *      (Symfony Mailer lanza error si no hay remitente).
     *
     * @param array|object|null $smtpParameters El request->smtp_parameters (opcional).
     * @return void
     */
    public function applyMailConfig($smtpParameters = null)
    {
        $smtp = collect($smtpParameters)->toArray();

        if(!empty($smtp)){
            \Config::set('mail.host', $smtp['host']);
            \Config::set('mail.port', $smtp['port']);
            \Config::set('mail.username', $smtp['username']);
            \Config::set('mail.password', $smtp['password']);
            \Config::set('mail.encryption', $smtp['encryption']);
            if(!empty($smtp['from_address']))
                \Config::set('mail.from.address', $smtp['from_address']);
            if(!empty($smtp['from_name']))
                \Config::set('mail.from.name', $smtp['from_name']);
        }
        elseif($this->validate_mail_server()){
            \Config::set('mail.host', $this->mail_host);
            \Config::set('mail.port', $this->mail_port);
            \Config::set('mail.username', $this->mail_username);
            \Config::set('mail.password', $this->mail_password);
            \Config::set('mail.encryption', $this->mail_encryption);
            if(!empty($this->mail_from_address))
                \Config::set('mail.from.address', $this->mail_from_address);
            if(!empty($this->mail_from_name))
                \Config::set('mail.from.name', $this->mail_from_name);
        }

        // Red de seguridad: Symfony Mailer exige un remitente. Solo si quedo vacio
        // (ni la empresa/request ni el .env lo definieron) usamos el usuario SMTP.
        if(empty(config('mail.from.address')))
            \Config::set('mail.from.address', config('mail.username'));
    }

    public function document_type()
    {
        return $this->belongsTo(HealthTypeDocumentIdentification::class, 'document_type_id');
    }

    public function getCompanyAttribute()
    {
        if ($this->relationLoaded('company') && $this->company()->exists()) {
            return $this->getRelation('company');
        }
        if ($this->relationLoaded('companies') && $this->companies->isNotEmpty()) {
            return $this->companies->first();
        }
        $direct = $this->company()->first();
        if ($direct) {
            return $direct;
        }
        $many = $this->companies()->first();
        if ($many) {
            return $many;
        }
        if (property_exists($this, 'attributes') && array_key_exists('company', $this->attributes)) {
            return $this->attributes['company'];
        }
        return null;
    }
}
