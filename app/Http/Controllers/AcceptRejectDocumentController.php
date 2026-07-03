<?php

namespace App\Http\Controllers;

use App\Company;
use App\Customer;
use App\ReceivedDocument;
use App\Document;
use App\User;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Requests\Api\SendEventRequest;
use App\Http\Requests\Api\SendEventDataRequest;
use App\Http\Controllers\Api\SendEventController;
use Illuminate\Validation\Rule;
use App\Traits\DocumentTrait;
use Storage;
use App\Services\StorageService;

class AcceptRejectDocumentController extends Controller
{
    use DocumentTrait;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function ShowViewAcceptRejectDocument(Request $request, $company_idnumber, $customer_idnumber, $prefix, $docnumber, $issuedate)
    {
        return view('acceptrejectdocument', compact('request', 'company_idnumber', 'customer_idnumber', 'prefix', 'docnumber', 'issuedate'));
    }

    protected function DownloadFile(Request $request)
    {
        {
            $u = new \App\Utils;
            if(strpos($request->file, 'Attachment-') === false and strpos($request->file, 'ZipAttachm-') === false)
                if(StorageService::existsAuto("public/{$request->identification}/{$request->file}"))
                    if($request->type_response && $request->type_response === 'BASE64')
                        return [
                            'success' => true,
                            'message' => "Archivo: ".$request->file." se encontro.",
                            'filebase64'=>StorageService::getBase64AutoFallback("public/{$request->identification}/{$request->file}")
                        ];
                    else
                        return StorageService::downloadAuto("public/{$request->identification}/{$request->file}");
                else
                    return [
                        'success' => false,
                        'message' => "No se encontro el archivo: ".$request->file
                    ];
            else{
                if(strpos($request->file, 'ZipAttachm-') === false){
                    $filename = $u->attacheddocumentname($request->identification, $request->file);
                    if(StorageService::existsAuto("public/{$request->identification}/{$filename}.xml"))
                        if($request->type_response && $request->type_response === 'BASE64')
                            return [
                                'success' => true,
                                'message' => "Archivo: ".$filename.".xml se encontro.",
                                'filebase64'=>StorageService::getBase64AutoFallback("public/{$request->identification}/{$filename}.xml")
                            ];
                        else
                            return StorageService::downloadAuto("public/{$request->identification}/{$filename}.xml");
                    else
                        return [
                            'success' => false,
                            'message' => "No se encontro el archivo: ".$filename.".xml"
                        ];
                }
                else{
                    $filename = $u->attacheddocumentname($request->identification, $request->file);
                    if(StorageService::existsAuto("public/{$request->identification}/{$filename}.zip"))
                        if($request->type_response && $request->type_response === 'BASE64')
                            return [
                                'success' => true,
                                'message' => "Archivo: ".$filename.".zip se encontro.",
                                'filebase64'=>StorageService::getBase64AutoFallback("public/{$request->identification}/{$filename}.zip")
                            ];
                        else
                            return StorageService::downloadAuto("public/{$request->identification}/{$filename}.zip");
                    else
                        return [
                            'success' => false,
                            'message' => "No se encontro el archivo: ".$filename.".zip"
                        ];
                }
            }
        }
    }

    protected function ExecuteAcceptRejectDocument(Request $request)
    {
        $u = new \App\Utils;
        $e = new SendEventController();
        if($request->eventcode == "5"){
            if(!is_null($request->prefix) && $request->prefix != '')
                $d = Document::where('identification_number', $request->company_idnumber)->where('customer', $request->customer_idnumber)->where('prefix', $request->prefix)->where('number', $request->docnumber)->where('state_document_id', 1)->firstOrFail();
            else
                $d = Document::where('identification_number', $request->company_idnumber)->where('customer', $request->customer_idnumber)->where('number', $request->docnumber)->where('state_document_id', 1)->firstOrFail();
            $filename = $u->attacheddocumentname($d->identification_number, "Attachment-{$d->prefix}{$d->number}.xml").".xml";
            $has_xml = StorageService::existsAuto('public/'.$d->identification_number.'/'.$filename);
        }
        else{
            if(!is_null($request->prefix) && $request->prefix != '')
                $d = ReceivedDocument::where('identification_number', $request->company_idnumber)->where('customer', $request->customer_idnumber)->where('prefix', $request->prefix)->where('number', $request->docnumber)->firstOrFail();
            else
                $d = ReceivedDocument::where('identification_number', $request->company_idnumber)->where('customer', $request->customer_idnumber)->where('number', $request->docnumber)->firstOrFail();
            $filename = $d->xml;
            $has_xml = ($filename && $filename !== 'no-attached-document' && StorageService::existsAuto('received/'.$d->customer.'/'.$d->xml));
        }

        // Si no hay XML attachment disponible, usar CUFE para enviar el evento via sendeventdata
        if(!$has_xml){
            $cufe = $request->cufe ?? $d->cufe;
            if(!$cufe){
                if($request->ajax() || $request->expectsJson())
                    return response()->json(['success' => false, 'message' => 'No se encontró el archivo XML adjunto ni el CUFE del documento para enviar el evento.']);
                return view('customerloginmensaje', ['titulo' => 'Error', 'mensaje' => 'No se encontró el archivo XML adjunto ni el CUFE del documento para enviar el evento.']);
            }

            if($request->eventcode == "2")
                $send = [
                    'event_id' => $request->eventcode,
                    'document_reference' => ['cufe' => $cufe],
                    'type_rejection_id' => $request->rejection_id
                ];
            else
                $send = [
                    'event_id' => $request->eventcode,
                    'document_reference' => ['cufe' => $cufe],
                ];

            $r_request = new SendEventDataRequest($send);
            if($request->eventcode == "5")
                $r = $e->sendeventdata($r_request, $d->identification_number);
            else
                $r = $e->sendeventdata($r_request, $d->customer);
        }
        else{
            $att_str = base64_encode(StorageService::get($request->eventcode == "5" ? 'public/'.$d->identification_number.'/'.$filename : 'received/'.$d->customer.'/'.$d->xml));
            if($request->eventcode == "2")
                $send = [
                            'event_id' => $request->eventcode,
                            'base64_attacheddocument_name' => $filename,
                            'base64_attacheddocument' => $att_str,
                            'type_rejection_id' => $request->rejection_id
                        ];
            else
                $send = [
                            'event_id' => $request->eventcode,
                            'base64_attacheddocument_name' => $filename,
                            'base64_attacheddocument' => $att_str,
                        ];
            $data_send = json_encode($send);
            $r_request = new SendEventRequest($send);
            if($request->eventcode == "5")
                $r = $e->sendevent($r_request, $d->identification_number);
            else
                $r = $e->sendevent($r_request, $d->customer);
        }

        if($r['success'] == true)
            if($r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->IsValid == "false"){
                $message = $r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->StatusMessage;
                $errorMessage = $r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->ErrorMessage;
                if(isset($errorMessage->string)){
                    if(is_string($errorMessage->string)){
                        $message = $message." | ".$errorMessage->string;
                    }
                    else{
                        foreach($errorMessage->string as $m)
                            $message = $message." | ".$m;
                    }
                }
                if($request->ajax() || $request->expectsJson())
                    return response()->json(['success' => false, 'message' => $r['message'], 'detail' => $message]);
                return view('customerloginmensaje', ['titulo' => 'Resultado del Evento: '.$r['message'], 'mensaje' => nl2br($message)]);
            }
            else{
                if($request->ajax() || $request->expectsJson())
                    return response()->json(['success' => true, 'message' => $r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->StatusMessage]);
                return view('customerloginmensaje', ['titulo' => 'Resultado del Evento: '.$r['message'], 'mensaje' => $r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->StatusMessage]);
            }
        else{
            if($request->ajax() || $request->expectsJson())
                return response()->json(['success' => false, 'message' => $r['message']]);
            return view('customerloginmensaje', ['titulo' => 'Resultado del Evento: '.$r['message'], 'mensaje' => $r['message']]);
        }
    }
}

