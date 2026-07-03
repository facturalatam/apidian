<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Customer;

class PasswordSellerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $seller;
    public $password;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($seller, $password)
    {
        $this->seller = $seller;
        $this->password = $password;
        $this->user = User::where('id', $seller->user_id)->get()->first();
    }

    public function build()
    {
        if(env('MAIL_USERNAME'))
            return $this->view('mails.mailpasswordseller')->subject('Credenciales de Ingreso a la plataforma '.env("APP_NAME"))
                                                            ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'));
//                                                            ->from(env('MAIL_USERNAME'), env('APP_NAME'));
        else
            return $this->view('mails.mailpasswordseller')->subject('Credenciales de Ingreso a la plataforma '.env("APP_NAME"))
                                                            ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'));
//                                                            ->from(config('mail.username'), env('APP_NAME'));
    }
}
