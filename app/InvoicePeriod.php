<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoicePeriod extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start_date', 'end_date',
    ];
}
