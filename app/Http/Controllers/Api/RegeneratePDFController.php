<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Company;
use App\TaxTotal;
use App\InvoiceLine;
use App\InvoiceLine as CreditNoteLine;
use App\InvoiceLine as DebitNoteLine;
use App\BillingReference;
use App\PaymentForm;
use App\TypeDocument;
use App\TypeCurrency;
use App\TypeOperation;
use App\PaymentMethod;
use App\AllowanceCharge;
use App\LegalMonetaryTotal;
use App\PrepaidPayment;
use App\Municipality;
use App\OrderReference;
use App\Establishment;
use App\Document;
use Illuminate\Http\Request;
use App\Http\Requests\Api\InvoiceRequest;
use App\Traits\DocumentTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\StateController;
use App\Http\Requests\Api\RegeneratePDFRequest;
use App\Http\Requests\Api\StatusRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Mail\InvoiceMail;
use Carbon\Carbon;
use DateTime;
use App\Services\StorageService;

class RegeneratePDFController extends Controller
{
    use DocumentTrait;

    protected function query_dian_state($cufe, $ispayroll = FALSE){
        try{
            $req_state_query = [
                'is_payroll' => $ispayroll,
                'sendmail' => false,
                'sendmailtome' => false,
            ];

            if(isset($cufe) && $cufe != ""){
                $r = new StatusRequest($req_state_query);
                $q = new StateController();
                $r = $q->document($r, $cufe)['ResponseDian'];
                return $r;
            }
            else
                return [
                    'success' => false,
                    'message' => "Debe enviar la propiedad CUFE en el JSON request...",
                ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function validate_document_url($prefix, $number, $cufe, $company){
        try{
            $ispayroll = FALSE;
            //Document
            $invoice_doc = Document::where('cufe', $cufe)->where('state_document_id', 1)->get();
            if(count($invoice_doc) == 0){
                $invoice_doc = Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('prefix', $prefix)->where('number', $number)->get();
                if(count($invoice_doc) == 0)
                    return [
                        'success' => false,
                        'message' => 'Documento no encontrado...',
                    ];
            }
            $r = $this->query_dian_state($cufe, $ispayroll);
//            \Log::debug(json_encode($r));
            $nf = "";
            if(json_encode($r->Envelope->Body->GetStatusResponse->GetStatusResult->IsValid) == json_encode('true')){
                $nf = json_decode(str_replace("La Nomima Individual De Ajustes ", "", str_replace("-", "", str_replace("La Nomina Individual ", "", str_replace("La Documento Soporte Electr\u00f3nico ", "", str_replace("La Nota de d\u00e9bito electr\u00f3nica ", "", str_replace("La Nota de cr\u00e9dito electr\u00f3nica ", "", str_replace("de contingencia ", "", str_replace("de exportaci\u00f3n ", "", str_replace(", ha sido autorizada.", "", str_replace("La Factura electr\u00f3nica ", "", json_encode($r->Envelope->Body->GetStatusResponse->GetStatusResult->StatusMessage))))))))))));
//                \Log::debug($nf);
                $n = $invoice_doc[0]->number;
                $uuid = $invoice_doc[0]->cufe;

                if($nf != $invoice_doc[0]->prefix.$n)
                    return [
                        'success' => false,
                        'message' => "El CUFE en el request esta validado en la DIAN para el documento {$nf}, pero no corresponde al prefijo {$invoice_doc[0]->prefix} y numero {$invoice_doc[0]->number} del documento en la base de datos...",
                    ];
            }
            else
                return [
                    'success' => false,
                    'message' => "El CUFE en el request no esta validado en la DIAN, TrackId no existe en los registros de la DIAN...",
                ];
            if($nf != $prefix.$number)
                return [
                    'success' => false,
                    'message' => "El CUFE en el request esta validado en la DIAN para el documento {$nf}, pero no corresponde al prefijo {$prefix} y numero {$number} enviados en el JSON request...",
                ];

            if(json_encode($uuid) != json_encode($r->Envelope->Body->GetStatusResponse->GetStatusResult->XmlDocumentKey)){
                $invoice_doc[0]->cufe = $cufe;
                $invoice_doc[0]->save();
            }

            return [
                'success' => true,
                'invoice_doc' => $invoice_doc,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function validate_document_request($request, $company){
        try{
            $ispayroll = FALSE;
            //Document
            $invoice_doc = Document::where('cufe', $request->cufe)->where('state_document_id', 1)->get();
            if(count($invoice_doc) == 0){
                $invoice_doc = Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('prefix', $request->prefix)->where('number', $request->number)->get();
                if(count($invoice_doc) == 0)
                    return [
                        'success' => false,
                        'message' => 'Documento no encontrado...',
                    ];
            }
            $r = $this->query_dian_state($request->cufe, $ispayroll);
            $nf = "";
            if(json_encode($r->Envelope->Body->GetStatusResponse->GetStatusResult->IsValid) == json_encode('true')){
                $nf = json_decode(str_replace("-", "", str_replace("La Nomina Individual ", "", str_replace("La Documento Soporte Electr\u00f3nico ", "", str_replace("La Nota de d\u00e9bito electr\u00f3nica ", "", str_replace("La Nota de cr\u00e9dito electr\u00f3nica ", "", str_replace("de contingencia ", "", str_replace("de exportaci\u00f3n ", "", str_replace(", ha sido autorizada.", "", str_replace("La Factura electr\u00f3nica ", "", json_encode($r->Envelope->Body->GetStatusResponse->GetStatusResult->StatusMessage)))))))))));
                $n = $invoice_doc[0]->number;
                $uuid = $invoice_doc[0]->cufe;
                if($nf != $invoice_doc[0]->prefix.$n)
                    return [
                        'success' => false,
                        'message' => "El CUFE en el request esta validado en la DIAN para el documento {$nf}, pero no corresponde al prefijo {$invoice_doc[0]->prefix} y numero {$invoice_doc[0]->number} del documento en la base de datos...",
                    ];
            }
            else
                return [
                    'success' => false,
                    'message' => "El CUFE en el request no esta validado en la DIAN, TrackId no existe en los registros de la DIAN...",
                ];

            if($nf != $request->prefix.$request->number)
                return [
                    'success' => false,
                    'message' => "El CUFE en el request esta validado en la DIAN para el documento {$nf}, pero no corresponde al prefijo {$request->prefix} y numero {$request->number} enviados en el JSON request...",
                ];

            if(json_encode($uuid) != json_encode($r->Envelope->Body->GetStatusResponse->GetStatusResult->XmlDocumentKey)){
                $invoice_doc[0]->cufe = $request->cufe;
                $invoice_doc[0]->save();
            }

            return [
                'success' => true,
                'invoice_doc' => $invoice_doc
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

//    Se debe modificar el siguiente archivo de laravel
//    vendor\laravel\framework\src\Illuminate\Http\Request.php
//    MODIFICACION PARA REGENERAR PDF

//        /**
//         * Determine if the given offset exists.
//         *
//         * @param  string  $offset
//         * @return bool
//         */
//        public function offsetExists($offset)
//        {
//            if(!is_null($this->route()))
//                return Arr::has(
//                    $this->all() + $this->route()->parameters(),
//                    $offset
//                );
//            else
//                return TRUE;
//        }

        protected function regenerate_document($invoice_doc, $request = false){
        try{

            // User
            $user = auth()->user();

            // User company
            $company = $user->company;

            if(!$request){
                $request_db = new Request(json_decode($invoice_doc[0]->request_api, true));
                $request = RegeneratePDFRequest::create('api/ubl2.1/regeneratepdf/invoice', 'POST', $request_db->all());
//                $request->setContainer(app())->setRedirector(app(\Illuminate\Routing\Redirector::class))->validateResolved();
                $request->count_resolutions = auth()->user()->company->resolutions->where('type_document_id', $request->type_document_id)->count();
                if($request->count_resolutions < 2)
                    $request->resolution = auth()->user()->company->resolutions->where('type_document_id', $request->type_document_id)->first();
                else{
                    $request->count_resolutions = auth()->user()->company->resolutions->where('type_document_id', $request->type_document_id)->where('resolution', $request->resolution_number)->count();
                    if($request->count_resolutions < 2)
                        $request->resolution = auth()->user()->company->resolutions->where('type_document_id', $request->type_document_id)->where('resolution', $request->resolution_number)->first();
                    else
                        $request->resolution = auth()->user()->company->resolutions->where('type_document_id', $request->type_document_id)->where('resolution', $request->resolution_number)->where('prefix', $request->prefix)->first();
                }
                $request->cufe = $invoice_doc[0]->cufe_url;
            }

            // Type document
            $typeDocument = TypeDocument::findOrFail($request->type_document_id);

            if(in_array($request->type_document_id, array(1, 2, 3, 4, 5, 12))){
                // Customer
                $customerAll = collect($request->customer);
                if(isset($customerAll['municipality_id_fact']))
                    $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
                $customer = new User($customerAll->toArray());

                // Customer company
                $customer->company = new Company($customerAll->toArray());
            }
            else
                if(in_array($request->type_document_id, array(11, 13))){
                    // Seller
                    $sellerAll = collect($request->seller);
                    if(isset($sellerAll['municipality_id_fact']))
                        $sellerAll['municipality_id'] = Municipality::where('codefacturador', $sellerAll['municipality_id_fact'])->first();
                    $seller = new User($sellerAll->toArray());

                    // Seller company
                    $seller->company = new Company($sellerAll->toArray());
                    $seller->postal_zone_code = $sellerAll['postal_zone_code'];
                }

            // Delivery
            if($request->delivery){
                $deliveryAll = collect($request->delivery);
                $delivery = new User($deliveryAll->toArray());

                // Delivery company
                $delivery->company = new Company($deliveryAll->toArray());

                // Delivery party
                $deliverypartyAll = collect($request->deliveryparty);
                $deliveryparty = new User($deliverypartyAll->toArray());

                // Delivery party company
                $deliveryparty->company = new Company($deliverypartyAll->toArray());
            }
            else{
                $delivery = NULL;
                $deliveryparty = NULL;
            }

            // Type operation id
            if(!isset($request->type_operation_id) || !$request->type_operation_id)
                if(strpos($request, "agentparty_dv") != 0)
                    $request->type_operation_id = 11;
                else
                    if(isset($request->noteAIU))
                        $request->type_operation_id = 9;
                    else
                        $request->type_operation_id = 10;

            $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

            // Currency id
            if($request->idcurrency){
                $idcurrency = TypeCurrency::findOrFail($request->idcurrency);
                $calculationrate = $request->calculationrate;
                $calculationratedate = $request->calculationratedate;
            }
            else{
                if($request->type_document_id != 9 && $request->type_document_id != 10){
                    $idcurrency = TypeCurrency::findOrFail($invoice_doc[0]->currency_id);
                    $calculationrate = 1;
                    $calculationratedate = Carbon::now()->format('Y-m-d');
                }
            }

            // Resolution
            if($request->type_document_id != 9 && $request->type_document_id != 10)
                $request->resolution->number = $request->number;
            else
                $request->resolution->number = $request->consecutive;
            $resolution = $request->resolution;

            // Date time
            $date = $request->date;
            $time = $request->time;

            // Notes
            $notes = $request->notes;

            // Order Reference
            if($request->order_reference)
                $orderreference = new OrderReference($request->order_reference);
            else
                $orderreference = NULL;

            // Health Fields (Enterprise) deshabilitado en Community
            $healthfields = NULL;

            // Discrepancy response
            if(isset($request->discrepancyresponsecode))
                $discrepancycode = $request->discrepancyresponsecode;
            else
                $discrepancycode = NULL;

            if(isset($request->discrepancyresponsedescription))
                $discrepancydescription = $request->discrepancyresponsedescription;
            else
                $discrepancydescription = NULL;

            // Payment form
            if(isset($request->payment_form['payment_form_id']))
                $paymentFormAll = [(array) $request->payment_form];
            else
                $paymentFormAll = $request->payment_form;

            $paymentForm = collect();
            foreach ($paymentFormAll ?? [$this->paymentFormDefault] as $paymentF) {
                $payment = PaymentForm::findOrFail($paymentF['payment_form_id']);
                $payment['payment_method_code'] = PaymentMethod::findOrFail($paymentF['payment_method_id'])->code;
                $payment['nameMethod'] = PaymentMethod::findOrFail($paymentF['payment_method_id'])->name;
                $payment['payment_due_date'] = $paymentF['payment_due_date'] ?? null;
                $payment['duration_measure'] = $paymentF['duration_measure'] ?? null;
                $paymentForm->push($payment);
            }

            // Allowance charges
            $allowanceCharges = collect();
            foreach ($request->allowance_charges ?? [] as $allowanceCharge) {
                $allowanceCharges->push(new AllowanceCharge($allowanceCharge));
            }

            // Tax totals
            $taxTotals = collect();
            foreach ($request->tax_totals ?? [] as $taxTotal) {
                $taxTotals->push(new TaxTotal($taxTotal));
            }

            // Retenciones globales
            $withHoldingTaxTotal = collect();
//            $withHoldingTaxTotalCount = 0;
//          $holdingTaxTotal = $request->holding_tax_total;
            foreach($request->with_holding_tax_total ?? [] as $item) {
//                $withHoldingTaxTotalCount++;
//                $holdingTaxTotal = $request->holding_tax_total;
                $withHoldingTaxTotal->push(new TaxTotal($item));
            }

            // Prepaid Payment
            if($request->prepaid_payment)
                $prepaidpayment = new PrepaidPayment($request->prepaid_payment);
            else
                $prepaidpayment = NULL;

            // Legal monetary totals
            if(in_array($request->type_document_id, array(1, 2, 3, 4, 11, 12, 13)))
                $legalMonetaryTotals = new LegalMonetaryTotal($request->legal_monetary_totals);

            // Requested monetary totals
            if($request->type_document_id == 5)
                $requestedMonetaryTotals = new LegalMonetaryTotal($request->requested_monetary_totals);

            // Invoice lines
            if(in_array($request->type_document_id, array(1, 2, 3, 11, 12, 13))){
                $invoiceLines = collect();
                foreach ($request->invoice_lines as $invoiceLine) {
                    $invoiceLines->push(new InvoiceLine($invoiceLine));
                    $il = new InvoiceLine($invoiceLine);
                    if(isset($il->agentparty_dv))
                        $request->type_operation_id = 11;
                }
            }

            if($request->type_document_id == 4){
                // Credit note lines
                $creditNoteLines = collect();
                foreach ($request->credit_note_lines as $creditNoteLine) {
                    $creditNoteLines->push(new CreditNoteLine($creditNoteLine));
                }
            }

            if($request->type_document_id == 5){
                // Debit note lines
                $debitNoteLines = collect();
                foreach ($request->debit_note_lines as $debitNoteLine) {
                    $debitNoteLines->push(new DebitNoteLine($debitNoteLine));
                }
            }

            // Billing reference
            if(!$request->billing_reference)
                $billingReference = NULL;
            else
                $billingReference = new BillingReference($request->billing_reference);

            // Splited Name
            $splited_name = $this->split_name($user->name);

            if(in_array($request->type_document_id, array(1, 2, 3, 12)))
                $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $request->cufe, "INVOICE", $withHoldingTaxTotal, $notes, $healthfields);

            if($request->type_document_id == 4)
                $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $request->cufe, "NC", $withHoldingTaxTotal, $notes, $healthfields);

            if($request->type_document_id == 5)
                $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $request->cufe, "ND", $withHoldingTaxTotal, $notes, $healthfields);

            if($request->type_document_id == 11)
                $QRStr = $this->createPDF($user, $company, $seller, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $request->cufe, "SUPPORTDOCUMENT", $withHoldingTaxTotal, $notes, $healthfields);

             if($request->type_document_id == 13)
                $QRStr = $this->createPDF($user, $company, $seller, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $request->cufe, "SUPPORTDOCUMENTNOTE", $withHoldingTaxTotal, $notes, NULL);

            return [
                'success' => true,
                'message' => "Archivo: ".$invoice_doc[0]->pdf." se encontro.",
                'filebase64'=>StorageService::getBase64Auto("public/{$invoice_doc[0]->identification_number}/{$invoice_doc[0]->pdf}")
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Regenerate pdf invoice by request.
     *
     * @param \App\Http\Requests\Api\InvoiceRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function document_request(InvoiceRequest $request)
    {
        try{

            // User
            $user = auth()->user();

            // User company
            $company = $user->company;

            $validation = $this->validate_document_request($request, $company);
            if($validation['success'] == true){
                $pdf = $this->regenerate_document($validation['invoice_doc'], $request);
                return($pdf);
            }
            else
                return $validation;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Regenerate pdf invoice by url.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function document_url($prefix, $number, $cufe)
    {
        try{

            // User
            $user = auth()->user();

            // User company
            $company = $user->company;

            $validation = $this->validate_document_url($prefix, $number, $cufe, $company);
            if($validation['success'] == true){
                $validation['invoice_doc'][0]->cufe_url = $cufe;
                $pdf = $this->regenerate_document($validation['invoice_doc']);
                return($pdf);
            }
            else
                return $validation;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
