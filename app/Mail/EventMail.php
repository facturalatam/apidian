<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Traits\DocumentTrait;
use App\Document;
use App\ReceivedDocument;
use App\Customer;
use App\Company;
use App\User;
use App\Services\StorageService;

class EventMail extends Mailable
{
    use DocumentTrait, Queueable, SerializesModels;

    public $document;
    public $sender;
    public $company;
    public $filename;
    public $request_in;
    public $user;
    public $event;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($document,  $sender,  $company, $event, $request_in = FALSE, $filename = FALSE)
    {
        $this->document = $document;
        $this->sender = $sender;
        $this->company  = $company;
        $this->filename = $filename;
        $this->request_in = $request_in;
        $this->event = $event;

        $this->user = User::where('id', Company::where('identification_number', $sender->company['identification_number'])->firstOrFail()->user_id)->firstOrFail();
    }

    public function build()
    {
        if(env('MAIL_USERNAME') and $this->user->validate_mail_server() == false){
            if($this->filename)
                $nameZIP = $this->zipEmailEvent(StorageService::localPath("public/{$this->sender->company['identification_number']}/{$this->filename}.xml"), StorageService::localPath("public/{$this->sender->company['identification_number']}/EVS-{$this->sender->company['identification_number']}{$this->document[0]->number}{$this->event->code}.pdf"));
            else
                $nameZIP = $this->zipEmailEvent(StorageService::localPath("public/{$this->sender->company['identification_number']}/{$this->filename}.xml"), StorageService::localPath("public/{$this->sender->company['identification_number']}/EVS-{$this->sender->company['identification_number']}{$this->document[0]->number}{$this->event->code}.pdf"));
            return $this->view('mails.mail_event')->subject("Evento: {$this->sender->company['identification_number']};{$this->sender->name};{$this->sender->company['identification_number']}{$this->document[0]->number}{$this->event->code};{$this->document[0]->type_document->code};{$this->sender->name}")
                                                  ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                            ->from(env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                            ->from(env('MAIL_USERNAME'))
                                            ->attach($nameZIP);
        }
        else{
            if($this->filename)
                $nameZIP = $this->zipEmailEvent(StorageService::localPath("public/{$this->sender->company['identification_number']}/{$this->filename}.xml"), StorageService::localPath("public/{$this->sender->company['identification_number']}/EVS-{$this->sender->company['identification_number']}{$this->document[0]->number}{$this->event->code}.pdf"));
            else
                $nameZIP = $this->zipEmailEvent(StorageService::localPath("public/{$this->sender->company['identification_number']}/{$this->filename}.xml"), StorageService::localPath("public/{$this->sender->company['identification_number']}/EVS-{$this->sender->company['identification_number']}{$this->document[0]->number}{$this->event->code}.pdf"));
            return $this->view('mails.mail_event')->subject("Evento: {$this->sender->company['identification_number']};{$this->sender->name};{$this->sender->company['identification_number']}{$this->document[0]->number}{$this->event->code};{$this->document[0]->type_document->code};{$this->sender->name}")
                                                  ->from(env('MAIL_FROM_ADDRESS', config('mail.username')), 'nomail@nomail.com')
//                                            ->from(env('MAIL_FROM_ADDRESS', config('mail.username')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                            ->from(config('mail.username'))
                                            ->attach($nameZIP);
        }
    }
}
