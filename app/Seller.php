<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $primaryKey = 'identification_number';
    protected $fillable = [
        'identification_number', 'dv', 'name', 'phone', 'address', 'email', 'password',
    ];
}
