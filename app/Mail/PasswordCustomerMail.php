<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Customer;

class PasswordCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;
    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customer, $password)
    {
        $this->customer = $customer;
        $this->password = $password;
    }

    public function build()
    {
        if(env('MAIL_USERNAME'))
            return $this->view('mails.mailpasswordcustomer')->subject('Credenciales de Ingreso a la plataforma '.env("APP_NAME"))
                                                            ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'));
//                                                            ->from(env('MAIL_USERNAME'), env('APP_NAME'));
        else
            return $this->view('mails.mailpasswordcustomer')->subject('Credenciales de Ingreso a la plataforma '.env("APP_NAME"))
                                                            ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'));
//                                                            ->from(config('mail.username'), env('APP_NAME'));
    }
}
