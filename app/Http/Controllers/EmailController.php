<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use Illuminate\Http\Request;
use Redirect,Response,DB,Config;
use Mail;
use stdClass;
use App\Mail\SendGraphicRepresentation;
use App\Document;
use App\Traits\DocumentTrait;

class EmailController extends Controller
{
    use DocumentTrait;

    protected function sendEmail()
    {
        $data = new stdClass();
        $data->title = 'chorizos.net';
        $data->invoice = new stdClass();
        $data->invoice->user = new stdClass();
        $data->invoice->user->email = 'a@b.com';
        /* dd($data); */

        Mail::to('q@a.com')->send(new InvoiceMail($data, null));
    }


    protected function send(Request $request)
    {
        try {

            // User
            $user = auth()->user();

            // User company
            $company = $user->company;
            /*$company_format = new stdClass();
            $company_format->name = $user->name;
            $company_format->identification_number = $company->identification_number;*/
            if($request->number_full){
              $prefix = substr($request->number_full, 0, strpos($request->number_full, '-'));
              $document = Document::where([
                  ['identification_number', $company->identification_number],
                  ['number', $request->number],
                  ['prefix', $prefix]
              ])->first();
            }
            else
                $document = Document::where([
                    ['identification_number', $company->identification_number],
                    ['number', $request->number]
                ])->first();

            Mail::to($request->email)->send(new SendGraphicRepresentation($user, $document));

            return [
                'success' => true,
                'message' => 'Email enviado con éxito.',
            ];
        }
        catch (Exception $e) {

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];

        }



    }
}
