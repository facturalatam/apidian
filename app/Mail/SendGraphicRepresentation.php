<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use App\Document;
use Mpdf\Mpdf;
use Storage;
use App\User;
use App\Traits\DocumentTrait;
use App\Services\StorageService;

class SendGraphicRepresentation extends Mailable
{
    use Queueable, SerializesModels, DocumentTrait;

    /**
     * User
     * @var \App\User
     */
    public $company;

    /**
     * Document
     * @var \App\Document
     */
    public $document;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $company, Document $document) {
        $this->company = $company;
        $this->document = $document;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $path_file = StorageService::localPath("public/{$this->document->identification_number}/{$this->document->pdf}");
        $path_file_xml = StorageService::localPath("public/{$this->document->identification_number}/{$this->document->xml}");
        return $this->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
                    ->markdown('emails.send.graphicRepresentation')
                    ->subject("{$this->company->name} - {$this->document->type_document->name} {$this->document->number}")
                    ->attach($path_file_xml, [
                                    'as' => "{$this->document->number}.xml",
                                    'mime' => 'application/xml',
                    ])
                        ->attach($path_file, [
                            'as' => "{$this->document->number}.pdf",
                            'mime' => 'application/pdf',
                        ]);
      //  ->attach(Storage::disk('tenant')->path("{$this->document->type_document->template}/{$this->document->xml}"));
    }
}
