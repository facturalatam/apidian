<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Customer;
use App\User;

class RetrievePasswordSellerMail extends Mailable
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
        $this->seller->newpassword = str_replace('/', '(slash)', $this->seller->newpassword);
        $this->password = $password;
        $this->user = User::where('id', $seller->user_id)->get()->first();
    }

    public function build()
    {
        if(env('MAIL_USERNAME'))
            return $this->view('mails.mailretrievepasswordseller')->subject('Recuperacion de Credenciales de Ingreso a la plataforma de empresas '.env("APP_NAME"))
                                                                  ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'));
//                                                                  ->from(env('MAIL_USERNAME'), env('APP_NAME'));
        else
            return $this->view('mails.mailretrievepasswordseller')->subject('Recuperacion de Credenciales de Ingreso a la plataforma de empresas '.env("APP_NAME"))
                                                                  ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'));
//                                                                  ->from(config('mail.username'), env('APP_NAME'));
    }
}
