<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthTypeDocumentIdentification extends Model
{
    protected $fillable = [
        'name', 'code',
    ];
}
