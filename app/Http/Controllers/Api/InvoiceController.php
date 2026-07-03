<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Company;
use App\TaxTotal;
use App\InvoiceLine;
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
use App\Document;
use Illuminate\Http\Request;
use App\Traits\DocumentTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api;
use App\Http\Requests\Api\InvoiceRequest;
use ubl21dian\XAdES\SignInvoice;
use ubl21dian\XAdES\SignAttachedDocument;
use ubl21dian\Templates\SOAP\SendBillAsync;
use ubl21dian\Templates\SOAP\SendBillSync;
use ubl21dian\Templates\SOAP\SendTestSetAsync;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use App\Services\StorageService;
use Carbon\Carbon;
use DateTime;
use DB;

class InvoiceController extends Controller
{
    use DocumentTrait;

    public function preeliminarview(InvoiceRequest $request)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        // Actualizar Tablas
        $this->ActualizarTablas();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Customer
        $customerAll = collect($request->customer);
        if(isset($customerAll['municipality_id_fact']))
            $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
        $customerAll = $this->resolveCustomerLocation($customerAll);
        $customer = new User($customerAll->toArray());

        // Customer company
        $customer->company = new Company($customerAll->toArray());

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
        if(!$request->type_operation_id)
          $request->type_operation_id = 10;
        $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

        // Currency id
        if(isset($request->idcurrency) and (!is_null($request->idcurrency))){
            $idcurrency = TypeCurrency::findOrFail($request->idcurrency);
            $calculationrate = $request->calculationrate;
            $calculationratedate = $request->calculationratedate;
        }
        else{
            $idcurrency = null;
            $calculationrate = null;
            $calculationratedate = null;
            // $idcurrency = TypeCurrency::findOrFail(35);
            // $calculationrate = 1;
            // $calculationratedate = Carbon::now()->format('Y-m-d');
        }

        // Resolution

        $request->resolution->number = $request->number;
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
        // $withHoldingTaxTotalCount = 0;
        // $holdingTaxTotal = $request->holding_tax_total;
        foreach($request->with_holding_tax_total ?? [] as $item) {
            // $withHoldingTaxTotalCount++;
            // $holdingTaxTotal = $request->holding_tax_total;
            $withHoldingTaxTotal->push(new TaxTotal($item));
        }

        // Prepaid Payment
        if($request->prepaid_payment)
            $prepaidpayment = new PrepaidPayment($request->prepaid_payment);
        else
            $prepaidpayment = NULL;

        // Legal monetary totals
        $legalMonetaryTotals = new LegalMonetaryTotal($request->legal_monetary_totals);

        // Invoice lines

        $invoiceLines = collect();
        foreach ($request->invoice_lines as $invoiceLine) {
            $invoiceLines->push(new InvoiceLine($invoiceLine));
        }

        $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, "", "INVOICE", $withHoldingTaxTotal, $notes, $healthfields);

        return [
            'success' => true,
            'message' => "Vista preeliminar #{$resolution->next_consecutive} generada con éxito",
            'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
            'base64invoicepdf'=>StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.pdf"),
        ];
    }

    /**
     * Store.
     *
     * @param \App\Http\Requests\Api\InvoiceRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(InvoiceRequest $request)
    {
        // User
        $user = auth()->user();
        // Configura SMTP y remitente (empresa/request -> .env -> usuario SMTP).
        $user->applyMailConfig($request->smtp_parameters);

        // User company
        $company = $user->company;

        // Verificar la disponibilidad de la DIAN antes de continuar
        $dian_url = $company->software->url;
        if (!$this->verificarEstadoDIAN($dian_url)) {
            // Manejar la indisponibilidad del servicio, por ejemplo:
            return [
                'success' => false,
                'message' => 'El servicio de la DIAN no está disponible en este momento. Por favor, inténtelo más tarde.',
            ];
        }

        // Verify Certificate
        $certificate_days_left = 0;
        $c = $this->verify_certificate();
        if(!$c['success'])
            return $c;
        else
            $certificate_days_left = $c['certificate_days_left'];

        if($company->type_plan->state == false)
            return [
                'success' => false,
                'message' => 'El plan en el que esta registrado la empresa se encuentra en el momento INACTIVO para enviar documentos electronicos...',
            ];

        if($company->state == false)
            return [
                'success' => false,
                'message' => 'La empresa se encuentra en el momento INACTIVA para enviar documentos electronicos...',
            ];

        if($company->type_plan->period != 0 && $company->absolut_plan_documents == 0){
            $firstDate = new DateTime($company->start_plan_date);
            $secondDate = new DateTime(Carbon::now()->format('Y-m-d H:i'));
            $intvl = $firstDate->diff($secondDate);
            switch($company->type_plan->period){
                case 1:
                    if($intvl->y >= 1 || $intvl->m >= 1 || $this->qty_docs_period() >= $company->type_plan->qty_docs_invoice)
                        return [
                            'success' => false,
                            'message' => 'La empresa ha llegado al limite de tiempo/documentos del plan por mensualidad, por favor renueve su membresia...',
                        ];
                case 2:
                    if($intvl->y >= 1 || $this->qty_docs_period() >= $company->type_plan->qty_docs_invoice)
                        return [
                            'success' => false,
                            'message' => 'La empresa ha llegado al limite de tiempo/documentos del plan por anualidad, por favor renueve su membresia...',
                        ];
                case 3:
                    if($this->qty_docs_period() >= $company->type_plan->qty_docs_invoice)
                        return [
                            'success' => false,
                            'message' => 'La empresa ha llegado al limite de documentos del plan por paquetes, por favor renueve su membresia...',
                        ];
            }
        }
        else{
            if($company->absolut_plan_documents != 0){
                if($this->qty_docs_period("ABSOLUT") >= $company->absolut_plan_documents)
                    return [
                        'success' => false,
                        'message' => 'La empresa ha llegado al limite de documentos del plan mixto, por favor renueve su membresia...',
                    ];
            }
        }

        // Actualizar Tablas
        $this->ActualizarTablas();

        //Document
        $invoice_doc = new Document();
        $invoice_doc->request_api = json_encode($request->all());
        $invoice_doc->response_api = null;
        $invoice_doc->state_document_id = 0;
        $invoice_doc->type_document_id = $request->type_document_id;
        $invoice_doc->number = $request->number;
        $invoice_doc->client_id = 1;
        $invoice_doc->client =  $request->customer ;
        $invoice_doc->currency_id = 35;
        $invoice_doc->date_issue = date("Y-m-d H:i:s");
        $invoice_doc->sale = 1000;
        $invoice_doc->total_discount = 100;
        $invoice_doc->taxes =  $request->tax_totals;
        $invoice_doc->total_tax = 150;
        $invoice_doc->subtotal = 800;
        $invoice_doc->total = 1200;
        $invoice_doc->version_ubl_id = 1;
        $invoice_doc->ambient_id = 1;
        $invoice_doc->identification_number = $company->identification_number;
        // $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Customer
        $customerAll = collect($request->customer);
        if(isset($customerAll['municipality_id_fact']))
            $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
        $customerAll = $this->resolveCustomerLocation($customerAll);
        $customer = new User($customerAll->toArray());

        // Customer company
        $customer->company = new Company($customerAll->toArray());

        if($customer->company->identification_number !== '222222222222' && isset($request->email_pos_customer))
            return[
                'success' => false,
                'message' => 'El campo email_pos_customer solo es valido cuando se envia para el nit 222222222222 - CONSUMIDOR FINAL.',
            ];

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
        if(!$request->type_operation_id)
          $request->type_operation_id = 10;
        $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

        // Currency id
        if(isset($request->idcurrency) and (!is_null($request->idcurrency))){
            $idcurrency = TypeCurrency::findOrFail($request->idcurrency);
            $calculationrate = $request->calculationrate;
            $calculationratedate = $request->calculationratedate;
        }
        else{
            $idcurrency = null;
            $calculationrate = null;
            $calculationratedate = null;
            // $idcurrency = TypeCurrency::findOrFail($invoice_doc->currency_id);
            // $calculationrate = 1;
            // $calculationratedate = Carbon::now()->format('Y-m-d');
        }

        // Resolution
        $request->resolution->number = $request->number;
        $resolution = $request->resolution;
        if(config('system_configuration.validate_before_sending')){
            $doc = Document::where('type_document_id', $request->type_document_id)->where('identification_number', $company->identification_number)->where('prefix', $resolution->prefix)->where('number', $request->number)->where('state_document_id', 1)->get();
            if(count($doc) > 0)
                return [
                    'success' => false,
                    'message' => 'Este documento ya fue enviado anteriormente, se registra en la base de datos.',
                    'customer' => $doc[0]->customer,
                    'cufe' => $doc[0]->cufe,
                    'sale' => $doc[0]->total,
                ];
        }

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
        // $withHoldingTaxTotalCount = 0;
        // $holdingTaxTotal = $request->holding_tax_total;
        foreach($request->with_holding_tax_total ?? [] as $item) {
            // $withHoldingTaxTotalCount++;
            // $holdingTaxTotal = $request->holding_tax_total;
            $withHoldingTaxTotal->push(new TaxTotal($item));
        }

        // Prepaid Payment
        if($request->prepaid_payment)
            $prepaidpayment = new PrepaidPayment($request->prepaid_payment);
        else
            $prepaidpayment = NULL;

        // Prepaid Payments
        $prepaidpayments = collect();
        foreach ($request->prepaid_payments ?? [] as $prepaidPayment) {
            $prepaidpayments->push(new PrepaidPayment($prepaidPayment));
        }

        // Legal monetary totals
        $legalMonetaryTotals = new LegalMonetaryTotal($request->legal_monetary_totals);

        // Invoice lines
        $invoiceLines = collect();
        foreach ($request->invoice_lines as $invoiceLine) {
            $invoiceLines->push(new InvoiceLine($invoiceLine));
        }

        // Create XML
        $invoice = $this->createXML(compact('user', 'company', 'customer', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'invoiceLines', 'allowanceCharges', 'legalMonetaryTotals', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'prepaidpayment', 'prepaidpayments', 'delivery', 'deliveryparty', 'request', 'idcurrency', 'calculationrate', 'calculationratedate', 'healthfields'));

        // Register Customer
        if(config('system_configuration.apply_send_customer_credentials'))
            $this->registerCustomer($customer, $request->sendmail);
        else
            $this->registerCustomer($customer, $request->send_customer_credentials);

        // Signature XML
        $signInvoice = new SignInvoice($company->certificate->path, $company->certificate->password);
        $signInvoice->softwareID = $company->software->identifier;
        $signInvoice->pin = $company->software->pin;
        $signInvoice->technicalKey = $resolution->technical_key;

        if ($request->GuardarEn){
            if (!is_dir($request->GuardarEn)) {
                mkdir($request->GuardarEn);
            }
        }
        else{
            StorageService::ensureDirectory("public/{$company->identification_number}");
        }

        if ($request->GuardarEn)
            $signInvoice->GuardarEn = $request->GuardarEn."\\FE-{$resolution->next_consecutive}.xml";
        else
            $signInvoice->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml");

        $sendBillSync = new SendBillSync($company->certificate->path, $company->certificate->password);
        $sendBillSync->To = $company->software->url;
        $sendBillSync->fileName = "{$resolution->next_consecutive}.xml";
        if ($request->GuardarEn)
            $sendBillSync->contentFile = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), $request->GuardarEn."\\FES-{$resolution->next_consecutive}");
        else
            $sendBillSync->contentFile = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), StorageService::tempPath("public/{$company->identification_number}/FES-{$resolution->next_consecutive}"));
        if($request->query_uuid)
            return [
                'success' => true,
                'message' => 'Consulta del UUID del documento: '.$request->prefix.'-'.$request->number.', realizada con exito: ',
                'uuid' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $signInvoice->ConsultarQRStr(),
            ];

        $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $signInvoice->ConsultarCUFE(), "INVOICE", $withHoldingTaxTotal, $notes, $healthfields);

        $invoice_doc->prefix = $resolution->prefix;
        $invoice_doc->customer = $customer->company->identification_number;
        $invoice_doc->xml = "FES-{$resolution->next_consecutive}.xml";
        $invoice_doc->pdf = "FES-{$resolution->next_consecutive}.pdf";
        $invoice_doc->client_id = $customer->company->identification_number;
        $invoice_doc->client =  $request->customer ;
        if(property_exists($request, 'id_currency'))
            $invoice_doc->currency_id = $request->id_currency;
        else
            $invoice_doc->currency_id = 35;
        $invoice_doc->date_issue = date("Y-m-d H:i:s");
        $invoice_doc->sale = $legalMonetaryTotals->payable_amount;
        $invoice_doc->total_discount = $legalMonetaryTotals->allowance_total_amount ?? 0;
        $invoice_doc->taxes =  $request->tax_totals;
        $invoice_doc->total_tax = $legalMonetaryTotals->tax_inclusive_amount - $legalMonetaryTotals->tax_exclusive_amount;
        $invoice_doc->subtotal = $legalMonetaryTotals->line_extension_amount;
        $invoice_doc->total = $legalMonetaryTotals->payable_amount;
        $invoice_doc->version_ubl_id = 2;
        $invoice_doc->ambient_id = $company->type_environment_id;
        $invoice_doc->identification_number = $company->identification_number;
        $invoice_doc->save();

        $filename = '';
        $respuestadian = '';
        $typeDocument = TypeDocument::findOrFail(7);
        // $xml = new \DOMDocument;
        $ar = new \DOMDocument;
        $at = '';
        if ($request->GuardarEn){
            try{
                $respuestadian = $sendBillSync->signToSend($request->GuardarEn."\\ReqFE-{$resolution->next_consecutive}.xml")->getResponseToObject($request->GuardarEn."\\RptaFE-{$resolution->next_consecutive}.xml");
                if(isset($respuestadian->html)) {
                    $message = 'El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde...';
                    return $this->responseStore(false, $message, $request, $invoice_doc, $signInvoice, $invoice, $respuestadian, $resolution, $company, $QRStr, $certificate_days_left, $filename);
                }

                if($respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid == 'true'){
                    $filename = str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlFileName)));
                    if($request->atacheddocument_name_prefix)
                        $filename = $request->atacheddocument_name_prefix.$filename;
                    $cufecude = $respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlDocumentKey;
                    $invoice_doc->state_document_id = 1;
                    $invoice_doc->cufe = $cufecude;
                    $invoice_doc->save();
                    $signedxml = StorageService::get("xml/{$company->id}/".$respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlFileName.".xml");
                    // $xml->loadXML($signedxml);
                    if(strpos($signedxml, "</Invoice>") > 0)
                        $td = '/Invoice';
                    else
                        if(strpos($signedxml, "</CreditNote>") > 0)
                            $td = '/CreditNote';
                        else
                            $td = '/DebitNote';
                    $appresponsexml = base64_decode($respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlBase64Bytes);
                    $ar->loadXML($appresponsexml);
                    $fechavalidacion = $ar->documentElement->getElementsByTagName('IssueDate')->item(0)->nodeValue;
                    $horavalidacion = $ar->documentElement->getElementsByTagName('IssueTime')->item(0)->nodeValue;
                    $document_number = $this->ValueXML($signedxml, $td."/cbc:ID/");
                    // Create XML AttachedDocument
                    $attacheddocument = $this->createXML(compact('user', 'company', 'customer', 'resolution', 'typeDocument', 'cufecude', 'signedxml', 'appresponsexml', 'fechavalidacion', 'horavalidacion', 'document_number'));

                    // Signature XML
                    $signAttachedDocument = new SignAttachedDocument($company->certificate->path, $company->certificate->password);
                    $signAttachedDocument->GuardarEn = $request->GuardarEn."\\{$filename}.xml";

                    $at = $signAttachedDocument->sign($attacheddocument)->xml;
                    // $at = str_replace("&gt;", ">", str_replace("&quot;", '"', str_replace("&lt;", "<", $at)));
                    $file = fopen($request->GuardarEn."\\{$filename}".".xml", "w");
                    // $file = fopen($GuardarEn."\\Attachment-".$this->valueXML($signedxml, $td."/cbc:ID/").".xml", "w");
                    fwrite($file, $at);
                    fclose($file);
                    if(isset($request->annexes))
                        $this->saveAnnexes($request->annexes, $filename);
                    $invoice = Document::where('identification_number', '=', $company->identification_number)
                                               ->where('customer', '=', $customer->company->identification_number)
                                               ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                               ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                               ->where('state_document_id', '=', 1)->get();
                    if(isset($request->sendmail)){
                        if($request->sendmail){
                            if((count($invoice) > 0 && $customer->company->identification_number != '222222222222') || (count($invoice) > 0 && isset($request->email_pos_customer))){
                                try{
                                    if(isset($request->email_pos_customer))
                                        Mail::to($request->email_pos_customer)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, TRUE, $request));
                                    else
                                        Mail::to($customer->email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, TRUE, $request));
                                    if($request->sendmailtome)
                                        Mail::to($user->email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
                                    if($request->email_cc_list){
                                        foreach($request->email_cc_list as $email)
                                            Mail::to($email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
                                    }
                                    $invoice[0]->send_email_success = 1;
                                    $invoice[0]->send_email_date_time = Carbon::now()->format('Y-m-d H:i');
                                    $invoice[0]->save();
                                } catch (\Exception $m) {
                                    \Log::debug($m->getMessage());
                                }
                            }
                        }
                    }
                }
                else{
                  $invoice = null;
                  $at = '';
                }
            } catch (\Exception $e) {
                // return $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian));
                \Log::error($e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian)));
                $message = "Problema con el documento : {$typeDocument->name} #{$resolution->next_consecutive}";
                return $this->responseStore(false, $message, $request, $invoice_doc, $signInvoice, $invoice, $respuestadian, $resolution, $company, $QRStr, $certificate_days_left, $filename, $at);
            }
            $message = "{$typeDocument->name} #{$resolution->next_consecutive} generada con éxito";
            return $this->responseStore(true, $message, $request, $invoice_doc, $signInvoice, $invoice, $respuestadian, $resolution, $company, $QRStr, $certificate_days_left, $filename, $at);
        }
        else{
            try{
                $respuestadian = $sendBillSync->signToSend(StorageService::tempPath("public/{$company->identification_number}/ReqFE-{$resolution->next_consecutive}.xml"))->getResponseToObject(StorageService::tempPath("public/{$company->identification_number}/RptaFE-{$resolution->next_consecutive}.xml"));
                if(isset($respuestadian->html)) {
                    $message = "El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde...";
                    return $this->responseStore(false, $message, $request, $invoice_doc, $signInvoice, $invoice, $respuestadian, $resolution, $company, $QRStr, $certificate_days_left, $filename);
                }

                // throw new \Exception('Forzando un error para probar el catch.');
                if($respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid == 'true'){
                    $filename = str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlFileName)));
                    if($request->atacheddocument_name_prefix)
                        $filename = $request->atacheddocument_name_prefix.$filename;
                    $cufecude = $respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlDocumentKey;
                    $invoice_doc->state_document_id = 1;
                    $invoice_doc->cufe = $cufecude;
                    $invoice_doc->save();
                    $signedxml = StorageService::get("xml/{$company->id}/".$respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlFileName.".xml");
                    // $xml->loadXML($signedxml);
                    if(strpos($signedxml, "</Invoice>") > 0)
                        $td = '/Invoice';
                    else
                        if(strpos($signedxml, "</CreditNote>") > 0)
                            $td = '/CreditNote';
                        else
                            $td = '/DebitNote';
                    $appresponsexml = base64_decode($respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlBase64Bytes);
                    $ar->loadXML($appresponsexml);
                    $fechavalidacion = $ar->documentElement->getElementsByTagName('IssueDate')->item(0)->nodeValue;
                    $horavalidacion = $ar->documentElement->getElementsByTagName('IssueTime')->item(0)->nodeValue;
                    $document_number = $this->ValueXML($signedxml, $td."/cbc:ID/");
                    // Create XML AttachedDocument
                    $attacheddocument = $this->createXML(compact('user', 'company', 'customer', 'resolution', 'typeDocument', 'cufecude', 'signedxml', 'appresponsexml', 'fechavalidacion', 'horavalidacion', 'document_number'));

                    // Signature XML
                    $signAttachedDocument = new SignAttachedDocument($company->certificate->path, $company->certificate->password);
                    $signAttachedDocument->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/{$filename}.xml");

                    $at = $signAttachedDocument->sign($attacheddocument)->xml;
                    // $at = str_replace("&gt;", ">", str_replace("&quot;", '"', str_replace("&lt;", "<", $at)));
                    $file = fopen(StorageService::tempPath("public/{$company->identification_number}/{$filename}".".xml"), "w");
                    // $file = fopen(StorageService::tempPath("public/{$company->identification_number}/Attachment-".$this->valueXML($signedxml, $td."/cbc:ID/").".xml"), "w");
                    fwrite($file, $at);
                    fclose($file);

                    // Upload generated files to S3
                    StorageService::uploadBatchIfS3([
                        "public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml",
                        "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml",
                        "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.zip",
                        "public/{$company->identification_number}/ReqFE-{$resolution->next_consecutive}.xml",
                        "public/{$company->identification_number}/RptaFE-{$resolution->next_consecutive}.xml",
                        "public/{$company->identification_number}/{$filename}.xml",
                    ]);
                    if(isset($request->annexes))
                        $this->saveAnnexes($request->annexes, $filename);
                    $invoice = Document::where('identification_number', '=', $company->identification_number)
                                               ->where('customer', '=', $customer->company->identification_number)
                                               ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                               ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                               ->where('state_document_id', '=', 1)->get();
                    if(isset($request->sendmail)){
                        if($request->sendmail){
                            if((count($invoice) > 0 && $customer->company->identification_number != '222222222222') || (count($invoice) > 0 && isset($request->email_pos_customer))){
                                try{
                                    if(isset($request->email_pos_customer))
                                        Mail::to($request->email_pos_customer)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, TRUE, $request));
                                    else
                                        Mail::to($customer->email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, TRUE, $request));
                                    if($request->sendmailtome)
                                        Mail::to($user->email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
                                    if($request->email_cc_list){
                                        foreach($request->email_cc_list as $email)
                                            Mail::to($email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
                                    }
                                    $invoice[0]->send_email_success = 1;
                                    $invoice[0]->send_email_date_time = Carbon::now()->format('Y-m-d H:i');
                                    $invoice[0]->save();
                                } catch (\Exception $m) {
                                    \Log::debug($m->getMessage());
                                }
                            }
                        }
                    }
                }
                else{
                  $invoice = null;
                  $at = '';
                  // Upload generated files to S3 even when rejected
                  StorageService::uploadBatchIfS3([
                      "public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml",
                      "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml",
                      "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.zip",
                      "public/{$company->identification_number}/ReqFE-{$resolution->next_consecutive}.xml",
                      "public/{$company->identification_number}/RptaFE-{$resolution->next_consecutive}.xml",
                  ]);
                }
            } catch (\Exception $e) {
                \Log::error($e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian)));
                $message = "Problema con el documento : {$typeDocument->name} #{$resolution->next_consecutive}";
                return $this->responseStore(false, $message, $request, $invoice_doc, $signInvoice, $invoice, $respuestadian, $resolution, $company, $QRStr, $certificate_days_left, $filename, $at);
            }
            $message = "{$typeDocument->name} #{$resolution->next_consecutive} generada con éxito";
            return $this->responseStore(true, $message, $request, $invoice_doc, $signInvoice, $invoice, $respuestadian, $resolution, $company, $QRStr, $certificate_days_left, $filename, $at);
        }
    }

    /**
     * GENERIC RESPONSE
     * se centraliza la respuesta
     * se permite guardar el cufe en casos de respuestas falsas
     * se guarda el response completo segun parametro de configuracion
     *
     * @param Bool $success
     * @param String $message
     * @param Illuminate\Http\Request $request
     * @param App\Document $invoice_doc
     * @param ubl21dian\XAdES\SignInvoice $signInvoice
     * @param Null|String $respuestadian
     * @param Object $resolution
     * @param App\Company $company
     * @param String $QRStr
     * @param Integer $certificate_days_left
     * @param String $filename
     *
     */
    public function responseStore($success, $message, $request, $invoice_doc, $signInvoice, $invoice, $respuestadian, $resolution, $company, $QRStr, $certificate_days_left, $filename, $at)
    {
        $generateCufe = $signInvoice->ConsultarCUFE();
        if(empty($invoice_doc->cufe)) {
            $invoice_doc->cufe = $generateCufe;
            $invoice_doc->save();
        }
        $whatsappResponse = null;
        if ($success && 
            isset($request->send_whatsapp) && 
            $request->send_whatsapp === true && 
            isset($respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode) && 
            $respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode === "00") {
            
            $pdfBase64 = StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.pdf");
            
            $whatsappController = new WhatsappConfigController();
            $whatsappRequest = new Request([
                'number' => $request->customer['phone'] ?? $request->number,
                'message' => "Factura #{$resolution->prefix}{$resolution->next_consecutive}",
                'file' => $pdfBase64,
                'filename' => "factura_{$resolution->prefix}{$resolution->next_consecutive}.pdf",
                'send_whatsapp' => true
            ]);
            
            $whatsappRequest->headers->set('Authorization', 'Bearer ' . $request->bearerToken());
            
            $whatsappResponse = $whatsappController->sendMessageWithPDF($whatsappRequest);
            $whatsappResponse = json_decode(json_encode($whatsappResponse->getData()), true);
        }
        $response = [
            'success' => $success,
            'message' => $message,
            'send_email_success' => (null !== $invoice && $request->sendmail == true) ?? $invoice[0]->send_email_success == 1,
            'send_email_date_time' => (null !== $invoice && $request->sendmail == true) ?? Carbon::now()->format('Y-m-d H:i'),
            'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
            'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
            'urlinvoiceattached'=>"{$filename}.xml",
            'cufe' => $generateCufe,
            'QRStr' => $QRStr,
            'certificate_days_left' => $certificate_days_left,
            'resolution_days_left' => $this->days_between_dates(Carbon::now()->format('Y-m-d'), $resolution->date_to),
            'ResponseDian' => $respuestadian,
            'whatsapp_response' => $whatsappResponse,
            'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml"),
            'zipinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.zip"),
            'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml"),
            'reqfe'=>StorageService::getBase64Auto("public/{$company->identification_number}/ReqFE-{$resolution->next_consecutive}.xml"),
            'rptafe'=>StorageService::getBase64Auto("public/{$company->identification_number}/RptaFE-{$resolution->next_consecutive}.xml"),
            'attacheddocument'=>base64_encode($at)
        ];

        if(config('system_configuration.save_response_dian_to_db')){
            $invoice_doc->response_api = json_encode($response);
        }
        $invoice_doc->response_dian = json_encode($respuestadian);
        $invoice_doc->save();

        return $response;
    }

    /**
     * Test set store.
     *
     * @param \App\Http\Requests\Api\InvoiceRequest $request
     * @param string                                $testSetId
     *
     * @return \Illuminate\Http\Response
     */
    public function testSetStore(InvoiceRequest $request, $testSetId)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        // Verificar la disponibilidad de la DIAN antes de continuar
        $dian_url = $company->software->url;
        if (!$this->verificarEstadoDIAN($dian_url)) {
            // Manejar la indisponibilidad del servicio, por ejemplo:
            return [
                'success' => false,
                'message' => 'El servicio de la DIAN no está disponible en este momento. Por favor, inténtelo más tarde.',
            ];
        }

        // Verify Certificate
        $certificate_days_left = 0;
        $c = $this->verify_certificate();
        if(!$c['success'])
            return $c;
        else
            $certificate_days_left = $c['certificate_days_left'];

        // Actualizar Tablas
        $this->ActualizarTablas();

        //Document
        $invoice_doc = new Document();
        $invoice_doc->request_api = json_encode($request->all());
        $invoice_doc->response_api = null;
        $invoice_doc->state_document_id = 0;
        $invoice_doc->type_document_id = $request->type_document_id;
        $invoice_doc->number = $request->number;
        $invoice_doc->client_id = 1;
        $invoice_doc->client =  $request->customer ;
        $invoice_doc->currency_id = 35;
        $invoice_doc->date_issue = date("Y-m-d H:i:s");
        $invoice_doc->sale = 1000;
        $invoice_doc->total_discount = 100;
        $invoice_doc->taxes =  $request->tax_totals;
        $invoice_doc->total_tax = 150;
        $invoice_doc->subtotal = 800;
        $invoice_doc->total = 1200;
        $invoice_doc->version_ubl_id = 1;
        $invoice_doc->ambient_id = 1;
        $invoice_doc->identification_number = $company->identification_number;
        // $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Customer
        $customerAll = collect($request->customer);
        if(isset($customerAll['municipality_id_fact']))
            $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
        $customerAll = $this->resolveCustomerLocation($customerAll);
        $customer = new User($customerAll->toArray());

        // Customer company
        $customer->company = new Company($customerAll->toArray());

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
        if(!$request->type_operation_id)
          $request->type_operation_id = 10;
        $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

        // Currency id
        if(isset($request->idcurrency) and (!is_null($request->idcurrency))){
            $idcurrency = TypeCurrency::findOrFail($request->idcurrency);
            $calculationrate = $request->calculationrate;
            $calculationratedate = $request->calculationratedate;
        }
        else{
            $idcurrency = null;
            $calculationrate = null;
            $calculationratedate = null;
            // $idcurrency = TypeCurrency::findOrFail($invoice_doc->currency_id);
            // $calculationrate = 1;
            // $calculationratedate = Carbon::now()->format('Y-m-d');
        }

        // Resolution

        $request->resolution->number = $request->number;
        $resolution = $request->resolution;

        if(config('system_configuration.validate_before_sending')){
            $doc = Document::where('type_document_id', $request->type_document_id)->where('identification_number', $company->identification_number)->where('prefix', $resolution->prefix)->where('number', $request->number)->where('state_document_id', 1)->get();
            if(count($doc) > 0)
                return [
                    'success' => false,
                    'message' => 'Este documento ya fue enviado anteriormente, se registra en la base de datos.',
                    'customer' => $doc[0]->customer,
                    'cufe' => $doc[0]->cufe,
                    'sale' => $doc[0]->total,
                ];
        }

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
        // $withHoldingTaxTotalCount = 0;
        // $holdingTaxTotal = $request->holding_tax_total;
        foreach($request->with_holding_tax_total ?? [] as $item) {
            // $withHoldingTaxTotalCount++;
            // $holdingTaxTotal = $request->holding_tax_total;
            $withHoldingTaxTotal->push(new TaxTotal($item));
        }

        // Prepaid Payment
        if($request->prepaid_payment)
            $prepaidpayment = new PrepaidPayment($request->prepaid_payment);
        else
            $prepaidpayment = NULL;

        // Prepaid Payments
        $prepaidpayments = collect();
        foreach ($request->prepaid_payments ?? [] as $prepaidPayment) {
            $prepaidpayments->push(new PrepaidPayment($prepaidPayment));
        }

        // Legal monetary totals
        $legalMonetaryTotals = new LegalMonetaryTotal($request->legal_monetary_totals);

        // Invoice lines

        $invoiceLines = collect();
        foreach ($request->invoice_lines as $invoiceLine) {
            $invoiceLines->push(new InvoiceLine($invoiceLine));
        }

        // Create XML
        $invoice = $this->createXML(compact('user', 'company', 'customer', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'invoiceLines', 'allowanceCharges', 'legalMonetaryTotals', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'prepaidpayment', 'prepaidpayments', 'delivery', 'deliveryparty', 'request', 'idcurrency', 'calculationrate', 'calculationratedate', 'healthfields'));

        // Register Customer
        if(config('system_configuration.apply_send_customer_credentials'))
            $this->registerCustomer($customer, $request->sendmail);
        else
            $this->registerCustomer($customer, $request->send_customer_credentials);

        // Signature XML
        $signInvoice = new SignInvoice($company->certificate->path, $company->certificate->password);
        $signInvoice->softwareID = $company->software->identifier;
        $signInvoice->pin = $company->software->pin;
        $signInvoice->technicalKey = $resolution->technical_key;

        if ($request->GuardarEn){
            if (!is_dir($request->GuardarEn)) {
                mkdir($request->GuardarEn);
            }
            $signInvoice->GuardarEn = $request->GuardarEn."\\FE-{$resolution->next_consecutive}.xml";
        }
        else{
            StorageService::ensureDirectory("public/{$company->identification_number}");
            $signInvoice->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml");
        }
        $sendTestSetAsync = new SendTestSetAsync($company->certificate->path, $company->certificate->password);
        $sendTestSetAsync->To = $company->software->url;
        $sendTestSetAsync->fileName = "{$resolution->next_consecutive}.xml";
        if ($request->GuardarEn)
          $sendTestSetAsync->contentFile = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), $request->GuardarEn."\\FES-{$resolution->next_consecutive}");
        else
          $sendTestSetAsync->contentFile = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), StorageService::tempPath("public/{$company->identification_number}/FES-{$resolution->next_consecutive}"));
        $sendTestSetAsync->testSetId = $testSetId;

        $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $signInvoice->ConsultarCUFE(), "INVOICE", $withHoldingTaxTotal, $notes, $healthfields);

        $invoice_doc->prefix = $resolution->prefix;
        $invoice_doc->customer = $customer->company->identification_number;
        $invoice_doc->xml = "FES-{$resolution->next_consecutive}.xml";
        $invoice_doc->pdf = "FES-{$resolution->next_consecutive}.pdf";
        $invoice_doc->client_id = $customer->company->identification_number;
        $invoice_doc->client =  $request->customer ;
        if(property_exists($request, 'id_currency'))
            $invoice_doc->currency_id = $request->id_currency;
        else
            $invoice_doc->currency_id = 35;
        $invoice_doc->date_issue = date("Y-m-d H:i:s");
        $invoice_doc->sale = $legalMonetaryTotals->payable_amount;
        $invoice_doc->total_discount = $legalMonetaryTotals->allowance_total_amount ?? 0;
        $invoice_doc->taxes =  $request->tax_totals;
        $invoice_doc->total_tax = $legalMonetaryTotals->tax_inclusive_amount - $legalMonetaryTotals->tax_exclusive_amount;
        $invoice_doc->subtotal = $legalMonetaryTotals->line_extension_amount;
        $invoice_doc->total = $legalMonetaryTotals->payable_amount;
        $invoice_doc->version_ubl_id = 2;
        $invoice_doc->ambient_id = $company->type_environment_id;
        $invoice_doc->identification_number = $company->identification_number;
        $invoice_doc->save();

        if ($request->GuardarEn){
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada con éxito",
                'ResponseDian' => $sendTestSetAsync->signToSend($request->GuardarEn."\\ReqFE-{$resolution->next_consecutive}.xml")->getResponseToObject($request->GuardarEn."\\RptaFE-{$resolution->next_consecutive}.xml"),
                'invoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FES-{$resolution->next_consecutive}.xml")),
                'zipinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FES-{$resolution->next_consecutive}.zip")),
                'unsignedinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FE-{$resolution->next_consecutive}.xml")),
                'reqfe'=>base64_encode(file_get_contents($request->GuardarEn."\\ReqFE-{$resolution->next_consecutive}.xml")),
                'rptafe'=>base64_encode(file_get_contents($request->GuardarEn."\\RptaFE-{$resolution->next_consecutive}.xml")),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'urlinvoiceattached'=>"Attachment-{$resolution->next_consecutive}.xml",
                'cufe' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $QRStr,
                'certificate_days_left' => $certificate_days_left,
                'resolution_days_left' => $this->days_between_dates(Carbon::now()->format('Y-m-d'), $resolution->date_to),
            ];
        }
        else{
            $respDian = $sendTestSetAsync->signToSend(StorageService::tempPath("public/{$company->identification_number}/ReqFE-{$resolution->next_consecutive}.xml"))->getResponseToObject(StorageService::tempPath("public/{$company->identification_number}/RptaFE-{$resolution->next_consecutive}.xml"));

            // Upload generated files to S3
            StorageService::uploadBatchIfS3([
                "public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.zip",
                "public/{$company->identification_number}/ReqFE-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/RptaFE-{$resolution->next_consecutive}.xml",
            ]);

            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada con éxito",
                'ResponseDian' => $respDian,
                'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml"),
                'zipinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.zip"),
                'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml"),
                'reqfe'=>StorageService::getBase64Auto("public/{$company->identification_number}/ReqFE-{$resolution->next_consecutive}.xml"),
                'rptafe'=>StorageService::getBase64Auto("public/{$company->identification_number}/RptaFE-{$resolution->next_consecutive}.xml"),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'urlinvoiceattached'=>"Attachment-{$resolution->next_consecutive}.xml",
                'cufe' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $QRStr,
                'certificate_days_left' => $certificate_days_left,
                'resolution_days_left' => $this->days_between_dates(Carbon::now()->format('Y-m-d'), $resolution->date_to),
            ];
        }
    }

    public function currentNumber($type, $prefix = null, $ignore_state_document_id = false)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;
        $resolution = $company->resolutions->where('type_document_id', $type)->first();

        if(is_null($prefix) || $prefix == "null"){
            //do nothing
        }else{
            $resolution = $company->resolutions->where('type_document_id', $type)->where('prefix', $prefix)->first();
        }

        try{
            if(!json_decode($ignore_state_document_id))
                    $maxValue = DB::table('documents')->where('identification_number', $company->identification_number)
                                                      ->where('type_document_id', $type)
                                                      ->where('prefix', $resolution->prefix)
                                                      ->where('state_document_id', 1)->max(DB::raw('CAST(number AS UNSIGNED)'));
            else
                    $maxValue = DB::table('documents')->where('identification_number', $company->identification_number)
                                                      ->where('type_document_id', $type)
                                                      ->where('prefix', $resolution->prefix)
                                                      ->max(DB::raw('CAST(number AS UNSIGNED)'));
            return [
                'number' => ($maxValue) ? ((int)$maxValue + 1) : (int)$resolution->from,
                'success' => true,
                'prefix' => $resolution->prefix
            ];
        } catch(\Exception $e) {
            return [
                'message' => "No se pudo realizar la operacion..",
                'success' => false,
                'Excepction' => $e->getMessage()
            ];
        }
    }

    public function changestateDocument($type, $number)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;
        $invoice = Document::where('identification_number', $company->identification_number)->where('type_document_id', $type)->where('state_document_id', 0)->where('number', $number)->latest()->first();
        if($invoice){
            $invoice->state_document_id = 1;
            $invoice->save();
        }
        return [
            'success' => true
        ];
    }
}
