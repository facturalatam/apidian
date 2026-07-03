<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class WhatsappConfig extends Model
{
    protected $fillable = [
        'user_id',
        'url_whatsapp',
        'token_whatsapp'
    ];

    protected $hidden = [
        'token_whatsapp'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}