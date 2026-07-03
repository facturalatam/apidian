<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TypeSPD extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'type_spds';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'code',
    ];
}
