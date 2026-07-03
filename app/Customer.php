<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $primaryKey = 'identification_number';
    protected $fillable = [
        'identification_number', 'dv', 'name', 'phone', 'address', 'email', 'password', 'newpassword','companies_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'companies_id');
    }
}
