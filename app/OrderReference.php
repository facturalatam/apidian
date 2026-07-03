<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderReference extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_order', 'issue_date_order',
    ];
}
