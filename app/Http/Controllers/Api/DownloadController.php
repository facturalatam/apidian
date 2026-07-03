<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StorageService;
use App\Traits\DocumentTrait;
use App\Document;
use App\Municipality;
use App\User;
use App\Company;
use App\PaymentForm;
use App\TypeDocument;
use App\TaxTotal;
use App\PaymentMethod;
use App\Http\Requests\Api\InvoiceRequest;
use Illuminate\Http\Request;
use Exception;


class DownloadController extends Controller
{

    use DocumentTrait;

    public function reloadPdf($identification, $file, $cufe)
    {

        try {

            $full_filename = explode('.', $file);

            if($full_filename[1] != 'pdf'){
                return [
                    'success' => false,
                    'message' => 'Tipo de archivo no válido'
                ];
            }


            $document = Document::where([['identification_number', $identification], ['pdf', $file]])->firstOrFail();

            $user = auth()->user();
            $company = $user->company;
            $request = json_decode($document->request_api);
            $typeDocument = TypeDocument::findOrFail($request->type_document_id);

            if(!in_array($request->type_document_id, [1,2,3])){
                return [
                    'success' => false,
                    'message' => 'Tipo de documento no válido'
                ];
            }

            // Customer
            $customerAll = collect($request->customer);
            if(isset($customerAll['municipality_id_fact'])){
                $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
            }

            $customer = new User($customerAll->toArray());

            // Customer company
            $customer->company = new Company($customerAll->toArray());

            // Resolution

            $count_resolutions = auth()->user()->company->resolutions->where('type_document_id', $request->type_document_id)->count();

            if($count_resolutions < 2){
                $request->resolution = auth()->user()->company->resolutions->where('type_document_id', $request->type_document_id)->first();
            }
            else{

                $count_resolutions = auth()->user()->company->resolutions->where('type_document_id', $request->type_document_id)->where('resolution', $request->resolution_number)->count();

                if($count_resolutions < 2){
                    $request->resolution = auth()->user()->company->resolutions->where('type_document_id', $request->type_document_id)->where('resolution', $request->resolution_number)->first();
                }
                else{
                    $request->resolution = auth()->user()->company->resolutions->where('type_document_id', $request->type_document_id)->where('resolution', $request->resolution_number)->where('prefix', $request->prefix)->first();
                }

            }

            $request->resolution->number = $request->number;
            $resolution = $request->resolution;
            // Resolution

            $date = $request->date;
            $time = $request->time;

            // dd( $request->payment_form);
            // return json_encode($request->payment_form);

            // Payment form default
            $paymentFormAll = $request->payment_form;
            // $paymentFormAll = (object) array_merge($this->paymentFormDefault, $request->payment_form ?? []);
            $paymentForm = PaymentForm::findOrFail($paymentFormAll->payment_form_id);
            $paymentForm->payment_method_code = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->code;
            $paymentForm->nameMethod = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->name;
            $paymentForm->payment_due_date = $paymentFormAll->payment_due_date ?? null;
            $paymentForm->duration_measure = $paymentFormAll->duration_measure ?? null;
            // Payment form default

            // Retenciones globales
            $withHoldingTaxTotal = collect();

            // return $request->with_holding_tax_total;
            $new_request = request()->merge(json_decode($document->request_api, true));

            foreach($new_request->with_holding_tax_total ?? [] as $item) {
                $withHoldingTaxTotal->push(new TaxTotal($item));
            }
            // Retenciones globales

            // Notes
            $notes = $request->notes ?? null;

            // $request->legal_monetary_totals = json_decode(json_encode($request->legal_monetary_totals), true);
            // $request->tax_totals = json_decode(json_encode($request->tax_totals), true);
            // $request->customer = json_decode(json_encode($request->customer), true);
            // $request->invoice_lines = json_decode(json_encode($request->invoice_lines), true);

            // $new_request = new InvoiceRequest(json_decode($document->request_api, true));

            // Ultimo parametro en NULL corresponde a los campos del sector salud, si se desean incluir se tendran que enviar.
            $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $new_request, $cufe, "INVOICE", $withHoldingTaxTotal, $notes, NULL);

            return [
                'success' => true,
                'message' => 'PDF regenerado correctamente'
            ];

        }
        catch(Exception $e) {

            return [
                'success' => false,
                'message' => "{$e->getLine()} - {$e->getMessage()}"
            ];

        }

    }

    /**
     * Descarga pública de archivos
     *
     * @param $identification
     * @param $file
     * @param $type_response
    */
    public function publicDownload($identification, $file, $type_response = false)
    {

//        if(!config('system_configuration.allow_public_download')){
          if(!env('ALLOW_PUBLIC_DONWLOAD', false)){
                $u = new \App\Utils;

            if(strpos($file, 'Attachment-') === false and strpos($file, 'ZipAttachm-') === false){

                // Usar existsAuto para buscar primero en local, luego en S3
                if(StorageService::existsAuto("public/{$identification}/{$file}"))
                    if($type_response && $type_response === 'BASE64')
                        return [
                            'success' => true,
                            'message' => "Archivo: ".$file." se encontro.",
                            'filebase64'=>StorageService::getBase64AutoFallback("public/{$identification}/{$file}")
                        ];
                    elseif($type_response && $type_response === 'INLINE')
                        return StorageService::inlineAuto("public/{$identification}/{$file}");
                    else
                        return StorageService::downloadAuto("public/{$identification}/{$file}");
                else
                    return [
                        'success' => false,
                        'message' => "No se encontro el archivo: ".$file
                    ];
            }
            else{
                if(strpos($file, 'ZipAttachm-') === false){
                    $filename = $u->attacheddocumentname($identification, $file);
                    if(StorageService::existsAuto("public/{$identification}/{$filename}.xml"))
                        if($type_response && $type_response === 'BASE64')
                            return [
                                'success' => true,
                                'message' => "Archivo: ".$filename.".xml se encontro.",
                                'filebase64'=>StorageService::getBase64AutoFallback("public/{$identification}/{$filename}.xml")
                            ];
                        else
                            return StorageService::downloadAuto("public/{$identification}/{$filename}.xml");
                    else
                        return [
                            'success' => false,
                            'message' => "No se encontro el archivo: ".$filename.".xml"
                        ];
                }
                else{
                    $filename = $u->attacheddocumentname($identification, $file);
                    if(StorageService::existsAuto("public/{$identification}/{$filename}.zip"))
                        if($type_response && $type_response === 'BASE64')
                            return [
                                'success' => true,
                                'message' => "Archivo: ".$filename.".zip se encontro.",
                                'filebase64'=>StorageService::getBase64AutoFallback("public/{$identification}/{$filename}.zip")
                            ];
                        else
                            return StorageService::downloadAuto("public/{$identification}/{$filename}.zip");
                    else
                        return [
                            'success' => false,
                            'message' => "No se encontro el archivo: ".$filename.".zip"
                        ];
                }
            }
        }else{
            return [
                'success' => false,
                'message' => 'La descarga pública de archivos se encuentra habilitada (API)'
            ];
        }
    }
}
