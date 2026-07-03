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
use App\Country;
use App\LegalMonetaryTotal;
use App\PrepaidPayment;
use App\Municipality;
use App\OrderReference;
use App\Document;
use Illuminate\Http\Request;
use App\Traits\DocumentTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SupportDocumentRequest;
use ubl21dian\XAdES\SignInvoice;
use ubl21dian\XAdES\SignAttachedDocument;
use ubl21dian\Templates\SOAP\SendBillAsync;
use ubl21dian\Templates\SOAP\SendBillSync;
use ubl21dian\Templates\SOAP\SendTestSetAsync;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use App\TypeDocumentIdentification;
use Carbon\Carbon;
use DateTime;
use App\Services\StorageService;

class SupportDocumentController extends Controller
{
    use DocumentTrait;

    /**
     * Store.
     *
     * @param \App\Http\Requests\Api\SupportDocumentRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SupportDocumentRequest $request)
    {
        // User
        $user = auth()->user();
        // Configura SMTP y remitente (empresa/request -> .env -> usuario SMTP).
        $user->applyMailConfig($request->smtp_parameters);

        // User company
        $company = $user->company;

        // Verificar la disponibilidad de la DIAN antes de continuar
        $dian_url = $company->software->url_support_document;
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

        if($company->type_plan4->state == false)
            return [
                'success' => false,
                'message' => 'El plan en el que esta registrado la empresa se encuentra en el momento INACTIVO para enviar documentos electronicos...',
            ];

        if($company->state == false)
            return [
                'success' => false,
                'message' => 'La empresa se encuentra en el momento INACTIVA para enviar documentos electronicos...',
            ];

        if($company->type_plan4->period != 0 && $company->absolut_plan_documents == 0){
            $firstDate = new DateTime($company->start_plan_date4);
            $secondDate = new DateTime(Carbon::now()->format('Y-m-d H:i'));
            $intvl = $firstDate->diff($secondDate);
            switch($company->type_plan4->period){
                case 1:
                    if($intvl->y >= 1 || $intvl->m >= 1 || $this->qty_docs_period("SUPPORT DOCUMENT") >= $company->type_plan4->qty_docs_ds)
                        return [
                            'success' => false,
                            'message' => 'La empresa ha llegado al limite de tiempo/documentos del plan por mensualidad, por favor renueve su membresia...',
                        ];
                case 2:
                    if($intvl->y >= 1 || $this->qty_docs_period("SUPPORT DOCUMENT") >= $company->type_plan4->qty_docs_ds)
                        return [
                            'success' => false,
                            'message' => 'La empresa ha llegado al limite de tiempo/documentos del plan por anualidad, por favor renueve su membresia...',
                        ];
                case 3:
                    if($this->qty_docs_period("SUPPORT DOCUMENT") >= $company->type_plan4->qty_docs_ds)
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
        $invoice_doc->state_document_id = 0;
        $invoice_doc->type_document_id = $request->type_document_id;
        $invoice_doc->number = $request->number;
        $invoice_doc->client_id = 1;
        $invoice_doc->client =  $request->seller ;
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
//        $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Seller
        $sellerAll = collect($request->seller);
        if(isset($sellerAll['municipality_id_fact']))
            $sellerAll['municipality_id'] = Municipality::where('codefacturador', $sellerAll['municipality_id_fact'])->first();
        $seller = new User($sellerAll->toArray());

        // Seller company
        $seller->company = new Company($sellerAll->toArray());
        $seller->postal_zone_code = $sellerAll['postal_zone_code'];

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
            $request->type_operation_id = 23;
        $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

        // Currency id
        if(isset($request->idcurrency) and (!is_null($request->idcurrency))){
            $idcurrency = TypeCurrency::findOrFail($request->idcurrency);
            $calculationrate = $request->calculationrate;
            $calculationratedate = $request->calculationratedate;
        }
        else{
            $idcurrency = TypeCurrency::findOrFail($invoice_doc->currency_id);
            $calculationrate = 1;
            $calculationratedate = Carbon::now()->format('Y-m-d');
        }

        // Resolution
        $request->resolution->number = $request->number;
        $resolution = $request->resolution;

        if(env('VALIDATE_BEFORE_SENDING', false)){
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
//        $withHoldingTaxTotalCount = 0;
//        $holdingTaxTotal = $request->holding_tax_total;
        foreach($request->with_holding_tax_total ?? [] as $item) {
//            $withHoldingTaxTotalCount++;
//            $holdingTaxTotal = $request->holding_tax_total;
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
        $invoice = $this->createXML(compact('user', 'company', 'seller', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'invoiceLines', 'allowanceCharges', 'legalMonetaryTotals', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'prepaidpayment', 'prepaidpayments', 'delivery', 'deliveryparty', 'request', 'idcurrency', 'calculationrate', 'calculationratedate', 'healthfields'));

        // Register Seller
        if(env('APPLY_SEND_CUSTOMER_CREDENTIALS', TRUE))
            $this->registerCustomer($seller, $request->sendmail);
        else
            $this->registerCustomer($seller, $request->send_customer_credentials);

        // Signature XML
        $signInvoice = new SignInvoice($company->certificate->path, $company->certificate->password);
        $signInvoice->softwareID = $company->software->identifier_support_document;
        $signInvoice->pin = $company->software->pin_support_document;
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
            $signInvoice->GuardarEn = $request->GuardarEn."\\DS-{$resolution->next_consecutive}.xml";
        else
            $signInvoice->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/DS-{$resolution->next_consecutive}.xml");

        $sendBillSync = new SendBillSync($company->certificate->path, $company->certificate->password);
        $sendBillSync->To = $company->software->url_support_document;
        $sendBillSync->fileName = "{$resolution->next_consecutive}.xml";
        if ($request->GuardarEn)
            $sendBillSync->contentFile = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), $request->GuardarEn."\\DSS-{$resolution->next_consecutive}");
        else
            $sendBillSync->contentFile = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), StorageService::tempPath("public/{$company->identification_number}/DSS-{$resolution->next_consecutive}"));

        $QRStr = $this->createPDF($user, $company, $seller, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $signInvoice->ConsultarCUDS(), "SUPPORTDOCUMENT", $withHoldingTaxTotal, $notes, $healthfields);

        $invoice_doc->prefix = $resolution->prefix;
        $invoice_doc->customer = $seller->company->identification_number;
        $invoice_doc->xml = "DSS-{$resolution->next_consecutive}.xml";
        $invoice_doc->pdf = "DSS-{$resolution->next_consecutive}.pdf";
        $invoice_doc->client_id = $seller->company->identification_number;
        $invoice_doc->client =  $request->seller ;
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
        $invoice_doc->ambient_id = $company->support_document_type_environment_id;
        $invoice_doc->identification_number = $company->identification_number;
        $invoice_doc->save();

        $filename = '';
        $respuestadian = '';
        $typeDocument = TypeDocument::findOrFail(7);
//        $xml = new \DOMDocument;
        $ar = new \DOMDocument;
        if ($request->GuardarEn){
            try{
                $respuestadian = $sendBillSync->signToSend($request->GuardarEn."\\ReqDS-{$resolution->next_consecutive}.xml")->getResponseToObject($request->GuardarEn."\\RptaDS-{$resolution->next_consecutive}.xml");
                if(isset($respuestadian->html))
                    return [
                        'success' => false,
                        'message' => "El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde..."
                    ];

                if($respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid == 'true'){
                    $filename = str_replace('ads', 'ad', str_replace('dse', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlFileName)))));
                    if($request->atacheddocument_name_prefix)
                        $filename = $request->atacheddocument_name_prefix.$filename;
                    $cufecude = $respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlDocumentKey;
                    $invoice_doc->state_document_id = 1;
                    $invoice_doc->cufe = $cufecude;
                    $invoice_doc->save();
                    $signedxml = StorageService::getAutoLocal("xml/{$company->id}/".$respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlFileName.".xml");
//                    $xml->loadXML($signedxml);
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
                    $attacheddocument = $this->createXML(compact('user', 'company', 'seller', 'resolution', 'typeDocument', 'cufecude', 'signedxml', 'appresponsexml', 'fechavalidacion', 'horavalidacion', 'document_number'));
                    // Signature XML
                    $signAttachedDocument = new SignAttachedDocument($company->certificate->path, $company->certificate->password);
                    $signAttachedDocument->GuardarEn = $request->GuardarEn."\\{$filename}.xml";

                    $at = $signAttachedDocument->sign($attacheddocument)->xml;
//                    $at = str_replace("&gt;", ">", str_replace("&quot;", '"', str_replace("&lt;", "<", $at)));
                    $file = fopen($request->GuardarEn."\\{$filename}".".xml", "w"); //Ajuste de $GuardarEn a $request->GuardarEn
//                    $file = fopen($GuardarEn."\\Attachment-".$this->valueXML($signedxml, $td."/cbc:ID/").".xml", "w");
                    fwrite($file, $at);
                    fclose($file);
                    if(isset($request->annexes))
                        $this->saveAnnexes($request->annexes, $filename);
                    $invoice = Document::where('identification_number', '=', $company->identification_number)
                                       ->where('customer', '=', $seller->company->identification_number)
                                       ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                       ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                       ->where('state_document_id', '=', 1)->get();
                    if(isset($request->sendmail)){
                        if($request->sendmail){
                            if(count($invoice) > 0 && $seller->company->identification_number != '222222222222'){
                                try{
                                    Mail::to($seller->email)->send(new InvoiceMail($invoice, $seller, $company, FALSE, FALSE, $filename, TRUE, $request));
                                    if($request->sendmailtome)
                                        Mail::to($user->email)->send(new InvoiceMail($invoice, $seller, $company, FALSE, FALSE, $filename, FALSE, $request));
                                    if($request->email_cc_list){
                                        foreach($request->email_cc_list as $email)
                                            Mail::to($email)->send(new InvoiceMail($invoice, $seller, $company, FALSE, FALSE, $filename, FALSE, $request));
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
                return $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian));
            }
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada con éxito",
                'send_email_success' => (null !== $invoice && $request->sendmail == true) ?? $invoice[0]->send_email_success == 1,
                'send_email_date_time' => (null !== $invoice && $request->sendmail == true) ?? Carbon::now()->format('Y-m-d H:i'),
                'ResponseDian' => $respuestadian,
                'invoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\DSS-{$resolution->next_consecutive}.xml")),
                'zipinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\DSS-{$resolution->next_consecutive}.zip")),
                'unsignedinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\DS-{$resolution->next_consecutive}.xml")),
                'reqfe'=>base64_encode(file_get_contents($request->GuardarEn."\\ReqDS-{$resolution->next_consecutive}.xml")),
                'rptafe'=>base64_encode(file_get_contents($request->GuardarEn."\\RptaDS-{$resolution->next_consecutive}.xml")),
                'attacheddocument'=>base64_encode($at),
                'urlinvoicexml'=>"DSS-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"DSS-{$resolution->next_consecutive}.pdf",
                'urlinvoiceattached'=>"{$filename}.xml",
                'cuds' => $signInvoice->ConsultarCUDS(),
                'QRStr' => $QRStr,
                'certificate_days_left' => $certificate_days_left,
                'resolution_days_left' => $this->days_between_dates(Carbon::now()->format('Y-m-d'), $resolution->date_to),
            ];
        }
        else{
            try{
                $respuestadian = $sendBillSync->signToSend(StorageService::tempPath("public/{$company->identification_number}/ReqDS-{$resolution->next_consecutive}.xml"))->getResponseToObject(StorageService::tempPath("public/{$company->identification_number}/RptaDS-{$resolution->next_consecutive}.xml"));
                if(isset($respuestadian->html))
                    return [
                        'success' => false,
                        'message' => "El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde..."
                    ];

                if($respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid == 'true'){
                    $filename = str_replace('ads', 'ad', str_replace('dse', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlFileName)))));
                    if($request->atacheddocument_name_prefix)
                        $filename = $request->atacheddocument_name_prefix.$filename;
                    $cufecude = $respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlDocumentKey;
                    $invoice_doc->state_document_id = 1;
                    $invoice_doc->cufe = $cufecude;
                    $invoice_doc->save();
                    $signedxml = file_get_contents(StorageService::tempPath("xml/{$company->id}/".$respuestadian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlFileName.".xml"));
//                    $xml->loadXML($signedxml);
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
                    $attacheddocument = $this->createXML(compact('user', 'company', 'seller', 'resolution', 'typeDocument', 'cufecude', 'signedxml', 'appresponsexml', 'fechavalidacion', 'horavalidacion', 'document_number'));
                    // Signature XML
                    $signAttachedDocument = new SignAttachedDocument($company->certificate->path, $company->certificate->password);
                    $signAttachedDocument->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/{$filename}.xml");

                    $at = $signAttachedDocument->sign($attacheddocument)->xml;
//                    $at = str_replace("&gt;", ">", str_replace("&quot;", '"', str_replace("&lt;", "<", $at)));
                    $file = fopen(StorageService::tempPath("public/{$company->identification_number}/{$filename}".".xml"), "w");
//                    $file = fopen(StorageService::tempPath("public/{$company->identification_number}/Attachment-".$this->valueXML($signedxml, $td."/cbc:ID/").".xml"), "w");
                    fwrite($file, $at);
                    fclose($file);
                    if(isset($request->annexes))
                        $this->saveAnnexes($request->annexes, $filename);
                    $invoice = Document::where('identification_number', '=', $company->identification_number)
                                       ->where('customer', '=', $seller->company->identification_number)
                                       ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                       ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                       ->where('state_document_id', '=', 1)->get();
                    if(isset($request->sendmail)){
                        if($request->sendmail){
                            if(count($invoice) > 0 && $seller->company->identification_number != '222222222222'){
                                try{
                                    Mail::to($seller->email)->send(new InvoiceMail($invoice, $seller, $company, FALSE, FALSE, $filename, TRUE));
                                    if($request->sendmailtome)
                                        Mail::to($user->email)->send(new InvoiceMail($invoice, $seller, $company, FALSE, FALSE, $filename, FALSE));
                                    if($request->email_cc_list){
                                        foreach($request->email_cc_list as $email)
                                            Mail::to($email)->send(new InvoiceMail($invoice, $seller, $company, FALSE, FALSE, $filename, FALSE));
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
                return $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian));
            }
            // Upload to S3 if configured
            StorageService::uploadBatchIfS3([
                "public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.zip",
                "public/{$company->identification_number}/DS-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/ReqDS-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/RptaDS-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.pdf",
                "public/{$company->identification_number}/{$filename}.xml",
            ]);
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada con éxito",
                'send_email_success' => (null !== $invoice && $request->sendmail == true) ?? $invoice[0]->send_email_success == 1,
                'send_email_date_time' => (null !== $invoice && $request->sendmail == true) ?? Carbon::now()->format('Y-m-d H:i'),
                'ResponseDian' => $respuestadian,
                'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.xml"),
                'zipinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.zip"),
                'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/DS-{$resolution->next_consecutive}.xml"),
                'reqfe'=>StorageService::getBase64Auto("public/{$company->identification_number}/ReqDS-{$resolution->next_consecutive}.xml"),
                'rptafe'=>StorageService::getBase64Auto("public/{$company->identification_number}/RptaDS-{$resolution->next_consecutive}.xml"),
                'attacheddocument'=>base64_encode($at),
                'urlinvoicexml'=>"DSS-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"DSS-{$resolution->next_consecutive}.pdf",
                'urlinvoiceattached'=>"{$filename}.xml",
                'cuds' => $signInvoice->ConsultarCUDS(),
                'QRStr' => $QRStr,
                'certificate_days_left' => $certificate_days_left,
                'resolution_days_left' => $this->days_between_dates(Carbon::now()->format('Y-m-d'), $resolution->date_to),
            ];
        }
    }

    /**
     * Test set store.
     *
     * @param \App\Http\Requests\Api\SupportDocumentRequest $request
     * @param string                                $testSetId
     *
     * @return \Illuminate\Http\Response
     */
    public function testSetStore(SupportDocumentRequest $request, $testSetId)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        // Verificar la disponibilidad de la DIAN antes de continuar
        $dian_url = $company->software->url_support_document;
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
        $invoice_doc->state_document_id = 0;
        $invoice_doc->type_document_id = $request->type_document_id;
        $invoice_doc->number = $request->number;
        $invoice_doc->client_id = 1;
        $invoice_doc->client =  $request->seller ;
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
//        $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Seller
        $sellerAll = collect($request->seller);
        if(isset($sellerAll['municipality_id_fact']))
            $sellerAll['municipality_id'] = Municipality::where('codefacturador', $sellerAll['municipality_id_fact'])->first();
        $seller = new User($sellerAll->toArray());
        if (isset($sellerAll['country_code']) && !empty($sellerAll['country_code'])) {
            $country = Country::where('code', 'LIKE', "%{$sellerAll['country_code']}%")->first();
            if ($country) {
                $sellerAll['country_id'] = $country->id;
            }
        }
        $sellerAll['country_id'] = $sellerAll['country_id'] ?? 46;

        // Seller company
        $seller->company = new Company($sellerAll->toArray());
        $seller->postal_zone_code = $sellerAll['postal_zone_code'];
        if (isset($sellerAll['type_document_identification_id'])) {
            $seller->company->type_document_identification = TypeDocumentIdentification::find($sellerAll['type_document_identification_id']);
        }
        if ($seller->company->country_id != 46) {
            $seller->company->municipality_name = $sellerAll['municipality_name'];
            $seller->company->state_name = $sellerAll['state_name'];
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
        if(!$request->type_operation_id)
            $request->type_operation_id = 23;
        $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

        // Currency id
        if(isset($request->idcurrency) and (!is_null($request->idcurrency))){
            $idcurrency = TypeCurrency::findOrFail($request->idcurrency);
            $calculationrate = $request->calculationrate;
            $calculationratedate = $request->calculationratedate;
        }
        else{
            $idcurrency = TypeCurrency::findOrFail($invoice_doc->currency_id);
            $calculationrate = 1;
            $calculationratedate = Carbon::now()->format('Y-m-d');
        }

        // Resolution

        $request->resolution->number = $request->number;
        $resolution = $request->resolution;

        if(env('VALIDATE_BEFORE_SENDING', false)){
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
//        $withHoldingTaxTotalCount = 0;
//        $holdingTaxTotal = $request->holding_tax_total;
        foreach($request->with_holding_tax_total ?? [] as $item) {
//            $withHoldingTaxTotalCount++;
//            $holdingTaxTotal = $request->holding_tax_total;
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
        $invoice = $this->createXML(compact('user', 'company', 'seller', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'invoiceLines', 'allowanceCharges', 'legalMonetaryTotals', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'prepaidpayment', 'prepaidpayments', 'delivery', 'deliveryparty', 'request', 'idcurrency', 'calculationrate', 'calculationratedate', 'healthfields'));

        // Register Seller
        $this->registerCustomer($seller, $request->sendmail);

        // Signature XML
        $signInvoice = new SignInvoice($company->certificate->path, $company->certificate->password);
        $signInvoice->softwareID = $company->software->identifier_support_document;
        $signInvoice->pin = $company->software->pin_support_document;
        $signInvoice->technicalKey = $resolution->technical_key;

        if ($request->GuardarEn){
            if (!is_dir($request->GuardarEn)) {
                mkdir($request->GuardarEn);
            }
            $signInvoice->GuardarEn = $request->GuardarEn."\\DS-{$resolution->next_consecutive}.xml";
        }
        else{
            StorageService::ensureDirectory("public/{$company->identification_number}");
            $signInvoice->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/DS-{$resolution->next_consecutive}.xml");
        }
        $sendTestSetAsync = new SendTestSetAsync($company->certificate->path, $company->certificate->password);
        $sendTestSetAsync->To = $company->software->url_support_document;
        $sendTestSetAsync->fileName = "{$resolution->next_consecutive}.xml";
        if ($request->GuardarEn)
          $sendTestSetAsync->contentFile = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), $request->GuardarEn."\\DSS-{$resolution->next_consecutive}");
        else
          $sendTestSetAsync->contentFile = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), StorageService::tempPath("public/{$company->identification_number}/DSS-{$resolution->next_consecutive}"));
        $sendTestSetAsync->testSetId = $testSetId;

        $QRStr = $this->createPDF($user, $company, $seller, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $signInvoice->ConsultarCUDS(), "SUPPORTDOCUMENT", $withHoldingTaxTotal, $notes, $healthfields);

        $invoice_doc->prefix = $resolution->prefix;
        $invoice_doc->customer = $seller->company->identification_number;
        $invoice_doc->xml = "DSS-{$resolution->next_consecutive}.xml";
        $invoice_doc->pdf = "DSS-{$resolution->next_consecutive}.pdf";
        $invoice_doc->client_id = $seller->company->identification_number;
        $invoice_doc->client =  $request->seller ;
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
        $invoice_doc->ambient_id = $company->support_document_type_environment_id;
        $invoice_doc->identification_number = $company->identification_number;
        $invoice_doc->save();

        if ($request->GuardarEn){
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada con éxito",
                'ResponseDian' => $sendTestSetAsync->signToSend($request->GuardarEn."\\ReqDS-{$resolution->next_consecutive}.xml")->getResponseToObject($request->GuardarEn."\\RptaDS-{$resolution->next_consecutive}.xml"),
                'invoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\DSS-{$resolution->next_consecutive}.xml")),
                'zipinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\DSS-{$resolution->next_consecutive}.zip")),
                'unsignedinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\DS-{$resolution->next_consecutive}.xml")),
                'reqfe'=>base64_encode(file_get_contents($request->GuardarEn."\\ReqDS-{$resolution->next_consecutive}.xml")),
                'rptafe'=>base64_encode(file_get_contents($request->GuardarEn."\\RptaDS-{$resolution->next_consecutive}.xml")),
                'urlinvoicexml'=>"DSS-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"DSS-{$resolution->next_consecutive}.pdf",
                'urlinvoiceattached'=>"Attachment-{$resolution->next_consecutive}.xml",
                'cuds' => $signInvoice->ConsultarCUDS(),
                'QRStr' => $QRStr,
                'certificate_days_left' => $certificate_days_left,
                'resolution_days_left' => $this->days_between_dates(Carbon::now()->format('Y-m-d'), $resolution->date_to),
            ];
        }
        else{
            // Upload to S3 if configured
            StorageService::uploadBatchIfS3([
                "public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.zip",
                "public/{$company->identification_number}/DS-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/ReqDS-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/RptaDS-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.pdf",
            ]);
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada con éxito",
                'ResponseDian' => $sendTestSetAsync->signToSend(StorageService::tempPath("public/{$company->identification_number}/ReqDS-{$resolution->next_consecutive}.xml"))->getResponseToObject(StorageService::tempPath("public/{$company->identification_number}/RptaDS-{$resolution->next_consecutive}.xml")),
                'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.xml"),
                'zipinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.zip"),
                'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/DS-{$resolution->next_consecutive}.xml"),
                'reqfe'=>StorageService::getBase64Auto("public/{$company->identification_number}/ReqDS-{$resolution->next_consecutive}.xml"),
                'rptafe'=>StorageService::getBase64Auto("public/{$company->identification_number}/RptaDS-{$resolution->next_consecutive}.xml"),
                'urlinvoicexml'=>"DSS-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"DSS-{$resolution->next_consecutive}.pdf",
                'urlinvoiceattached'=>"Attachment-{$resolution->next_consecutive}.xml",
                'cuds' => $signInvoice->ConsultarCUDS(),
                'QRStr' => $QRStr,
                'certificate_days_left' => $certificate_days_left,
                'resolution_days_left' => $this->days_between_dates(Carbon::now()->format('Y-m-d'), $resolution->date_to),
            ];
        }
    }
}
