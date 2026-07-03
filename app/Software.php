<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identifier', 'pin', 'url', 'identifier_payroll', 'pin_payroll', 'url_payroll', 'identifier_eqdocs', 'pin_eqdocs', 'url_eqdocs', 'identifier_support_document', 'pin_support_document', 'url_support_document', 'url_event',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'company_id',
    ];
}
