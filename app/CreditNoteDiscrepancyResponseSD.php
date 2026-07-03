<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditNoteDiscrepancyResponseSD extends Model
{
    protected $table = 'credit_note_discrepancy_responses_sd';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code',
    ];
}
