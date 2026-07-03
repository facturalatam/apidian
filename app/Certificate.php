<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Services\StorageService;

class Certificate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'password', 'expiration_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'company_id', 'path',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'path',
    ];

    /**
     * Get the certificate path (always local filesystem).
     * Certificates must always be local for OpenSSL signing.
     *
     * @return string
     */
    public function getPathAttribute()
    {
        return StorageService::localStoragePath("certificates/{$this->name}");
    }
}
