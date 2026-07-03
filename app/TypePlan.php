<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TypePlan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'qty_docs_invoice', 'qty_docs_payroll', 'qty_docs_radian', 'qty_docs_ds', 'period', 'state', 'observations',
    ];
}
