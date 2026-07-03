<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identification_number', 'dv', 'name', 'address', 'phone', 'email', 'contact_name', 'password', 'plan', 'state', 'observation',
    ];

}
