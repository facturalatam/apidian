<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\DocumentTrait;
use App\Mail\InvoiceMail;
use App\Mail\PasswordCustomerMail;
use App\User;
use App\Customer;
use App\Document;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Requests\Api\SendEmailRequest;
use Illuminate\Support\Facades\Mail;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Services\StorageService;

class SendEmailController extends Controller
{
    use DocumentTrait;

    /**
     * SendEmail.
     *
     *
     * @return array
     */

    public function SendEmail(SendEmailRequest $request, $GuardarEn = FALSE)
    {
        // User
        $ShowAcceptRejectButtons = FALSE;
        $user = auth()->user();
        // Configura SMTP y remitente (empresa/request -> .env -> usuario SMTP).
        $user->applyMailConfig($request->smtp_parameters);

        // User company
        $company = $user->company;

        $document = Document::where('identification_number', '=', $company->identification_number)
                            ->where('prefix', '=', $request->prefix)
                            ->where('number', '=', $request->number)
                            ->where('state_document_id', '=', 1)->get();
        if(sizeof($document) == 0)
            return [
                'message' => "Documento {$request->prefix}-{$request->number} no existe en la base de datos.",
                'success' => FALSE,
            ];

        if(isset($request->showacceptrejectbuttons))
            $ShowAcceptRejectButtons = $request->showacceptrejectbuttons;
        else
            $ShowAcceptRejectButtons = FALSE;

        $customer = Customer::findOrFail($document[0]->customer);
        if($request->alternate_email)
            $email = $request->alternate_email;
        else
            $email = $customer->email;

        if($document[0]->type_document_id == 1)
            $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaFE-".$document[0]->prefix.$document[0]->number.".xml");
        else
            if($document[0]->type_document_id == 4)
                $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaNC-".$document[0]->prefix.$document[0]->number.".xml");
            else
                if ($document[0]->type_document_id == 11)
                    $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaDS-".$document[0]->prefix.$document[0]->number.".xml");
                else
                    if ($document[0]->type_document_id == 15)
                        $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaPOS-".$document[0]->prefix.$document[0]->number.".xml");
                    else
                        if ($document[0]->type_document_id == 19)
                            $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaTTR-".$document[0]->prefix.$document[0]->number.".xml");
                        else
                            if ($document[0]->type_document_id == 24)
                                $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaSRV-".$document[0]->prefix.$document[0]->number.".xml");
                            else
                               $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaND-".$document[0]->prefix.$document[0]->number.".xml");

        $filename = str_replace('pos', 'ad', str_replace('ttr', 'ad', str_replace('srv', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $this->getTag($rptafe, 'XmlFileName')->nodeValue))))));
        try{
            if(!$request->only_send_to_cc_list){
                if (!empty($email)) {
                    if ($GuardarEn){
                        if($request->base64graphicrepresentation)
                            Mail::to($email)->send(new InvoiceMail($document, $customer, $company, $GuardarEn, $request->base64graphicrepresentation, $filename, $ShowAcceptRejectButtons, $request));
                        else
                            Mail::to($email)->send(new InvoiceMail($document, $customer, $company, $GuardarEn, FALSE, $filename, $ShowAcceptRejectButtons, $request));
                    }
                    else{
                        if($request->base64graphicrepresentation)
                            Mail::to($email)->send(new InvoiceMail($document, $customer, $company, FALSE, $request->base64graphicrepresentation, $filename, $ShowAcceptRejectButtons, $request));
                        else
                            Mail::to($email)->send(new InvoiceMail($document, $customer, $company, FALSE, FALSE, $filename, $ShowAcceptRejectButtons, $request));
                    }
                }
            }
            if($request->email_cc_list){
                foreach($request->email_cc_list as $email) {
                    // Soporta tanto array como string
                    $ccEmail = is_array($email) ? ($email['email'] ?? null) : $email;
                    if ($ccEmail) {
                        if($request->base64graphicrepresentation)
                            Mail::to($ccEmail)->send(new InvoiceMail($document, $customer, $company, FALSE, $request->base64graphicrepresentation, $filename, FALSE, $request));
                        else
                            Mail::to($ccEmail)->send(new InvoiceMail($document, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
                    }
                }
            }
            $document[0]->send_email_success = 1;
            if(is_null($document[0]->send_email_date_time))
                $document[0]->send_email_date_time = Carbon::now()->format('Y-m-d H:i');
            $document[0]->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return [
            'message' => 'Envio realizado con éxito',
            'success' => TRUE,
        ];
    }

    /**
     * SendEmail Customer.
     *
     *
     * @return view
     */

    public function SendEmailCustomer(SendEmailRequest $request, $ShowView = 'YES')
    {
        $company = Company::where('identification_number', '=', $request->company_idnumber)->first();
        // User
        $user = User::where('id', $company->user_id)->firstOrFail();
        // Configura SMTP y remitente (empresa/request -> .env -> usuario SMTP).
        $user->applyMailConfig($request->smtp_parameters);

        if(empty($company))
            return view('customerloginmensaje', ['titulo' => 'Error al realizar el envio.',
                                                'mensaje' => 'Esta compañia no existe en la base de datos.']);

        $document = Document::where('identification_number', '=', $company->identification_number)
                            ->where('prefix', '=', $request->prefix)
                            ->where('number', '=', $request->number)
                            ->where('state_document_id', '=', 1)->get();
        if(sizeof($document) == 0)
            return view('customerloginmensaje', ['titulo' => 'Error al realizar el envio.',
                                                'mensaje' => 'Este documento no existe en la base de datos.']);

        $customer = Customer::findOrFail($document[0]->customer);
        if($document[0]->type_document_id == 1)
            $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaFE-".$document[0]->prefix.$document[0]->number.".xml");
        else
            if($document[0]->type_document_id == 4)
                $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaNC-".$document[0]->prefix.$document[0]->number.".xml");
            else
                if ($document[0]->type_document_id == 11)
                    $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaDS-".$document[0]->prefix.$document[0]->number.".xml");
                else
                    $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaND-".$document[0]->prefix.$document[0]->number.".xml");

        if(isset($this->getTag($rptafe, 'ZipKey')->nodeValue))
            $rptafe = StorageService::getAutoLocal("public/{$company->identification_number}/RptaZIP-".$this->getTag($rptafe, 'ZipKey')->nodeValue.".xml");

        $filename = str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $this->getTag($rptafe, 'XmlFileName')->nodeValue)));
//        return $user->email;
        if($filename <> ''){
            if(isset($request->customerEmail))
                Mail::to($request->customerEmail)->send(new InvoiceMail($document, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
            else
                Mail::to($customer->email)->send(new InvoiceMail($document, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
            Mail::to($user->email)->send(new InvoiceMail($document, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
        }
        if($ShowView == 'YES')
//            return redirect('/homecustomers');
            if($filename <> '')
                return view('customerloginmensaje', ['titulo' => 'Envio realizado con exito.',
                                                     'mensaje' => 'El Documento se envio satisfactoriamente.']);
            else
                return view('customerloginmensaje', ['titulo' => 'Error al realizar el envio.',
                                                     'mensaje' => 'El Documento no se pudo enviar.']);
        else
            if($filename <> '')
                return [
                    'message' => 'Envio realizado con éxito',
                    'success' => TRUE,
                ];
            else
                return [
                    'message' => 'Error al realizar el envio',
                    'success' => FALSE,
                ];

    }

    /**
     * SendEmail Password Customer.
     *
     *
     * @return view
     */

    public function SendEmailPasswordCustomer(Request $request)
    {
        $customer = Customer::findOrFail($request->identification_number);
        Mail::to($customer->email)->send(new PasswordCustomerMail($customer));
        return [
            'message' => 'Correo electronico para credenciales, enviado con exito.',
            'success' => 'true',
        ];
//        return view('customerloginmensaje', ['titulo' => 'Envio realizado con exito.',
//                                            'mensaje' => 'El Documento se envio satisfactoriamente.']);
    }


    public function sendExternalDocument(Request $request)
    {
        try {
            $user = auth()->user();
            // Configura SMTP y remitente (empresa/request -> .env -> usuario SMTP).
            $user->applyMailConfig($request->smtp_parameters);

            $validate = Validator::make($request->all(), [
                'email' => 'required|email',
                'subject' => 'required|string|max:255',
                'message' => 'nullable|string',
                'document_base64' => 'required|string',
                'filename' => 'required|string|max:255',
                'document_type' => 'required|string|in:pdf,xml,zip'
            ]);

            if ($validate->fails()) {
                return response([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validate->errors()
                ], 422);
            }

            $document = base64_decode($request->document_base64);
            $messageText = $request->message ?: 'Adjunto encontrará el documento solicitado.';
            
            Mail::send('emails.external-document', [
                'messageText' => $messageText
            ], function($mail) use ($request, $document) {
                $mail->subject($request->subject);
                $mail->to($request->email);
                $mail->attachData($document, $request->filename, [
                    'mime' => $this->getMimeType($request->document_type)
                ]);
            });

            return response([
                'success' => true,
                'message' => 'Correo enviado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getMimeType($type)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'xml' => 'application/xml',
            'zip' => 'application/zip'
        ];
        return $mimeTypes[$type] ?? 'application/octet-stream';
    }

}
