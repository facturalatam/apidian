<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Company;
use App\TaxTotal;
use App\InvoiceLine;
use App\PaymentForm;
use App\TypeDocument;
use App\TypeOperation;
use App\PaymentMethod;
use App\AllowanceCharge;
use App\LegalMonetaryTotal;
use App\PrepaidPayment;
use App\Municipality;
use App\OrderReference;
use App\Document;
use App\TypeCurrency;
use Illuminate\Http\Request;
use App\Traits\DocumentTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InvoiceRequest;
use App\Http\Requests\Api\InvoiceAIURequest;
use App\Http\Requests\Api\InvoiceMandateRequest;
use App\Http\Requests\Api\InvoiceExportRequest;
use App\Http\Requests\Api\InvoiceContingencyRequest;
use ubl21dian\XAdES\SignInvoice;
use ubl21dian\Templates\SOAP\SendBillAsync;
use App\BillingReference;
use App\InvoiceLine as CreditNoteLine;
use App\Http\Requests\Api\CreditNoteRequest;
use ubl21dian\XAdES\SignCreditNote;
use App\InvoiceLine as DebitNoteLine;
use App\Http\Requests\Api\DebitNoteRequest;
use ubl21dian\XAdES\SignDebitNote;
use App\Services\StorageService;

class BatchController extends Controller
{
    use DocumentTrait;

    /**
     * Add Invoice to batch.
     *
     * @param \App\Http\Requests\Api\InvoiceRequest $request
     * @param string                                $batch
     *
     * @return \Illuminate\Http\Response
     */
    public function addinvoice(InvoiceRequest $request, $batch)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        // Actualizar Tablas

        $this->ActualizarTablas();

        //Document
        $invoice_doc = new Document();
        $invoice_doc->request_api = json_encode($request->all());
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
//        $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Customer
        $customerAll = collect($request->customer);
        if(isset($customerAll['municipality_id_fact']))
            $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
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

        // Payment form default
        $paymentFormAll = (object) array_merge($this->paymentFormDefault, $request->payment_form ?? []);
        $paymentForm = PaymentForm::findOrFail($paymentFormAll->payment_form_id);
        $paymentForm->payment_method_code = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->code;
        $paymentForm->nameMethod = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->name;
        $paymentForm->payment_due_date = $paymentFormAll->payment_due_date ?? null;
        $paymentForm->duration_measure = $paymentFormAll->duration_measure ?? null;

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
        foreach($request->with_holding_tax_total ?? [] as $item) {
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
        $invoice = $this->createXML(compact('user', 'company', 'customer', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'invoiceLines', 'allowanceCharges', 'legalMonetaryTotals', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'prepaidpayment', 'prepaidpayments', 'delivery', 'deliveryparty', 'request'));

        // Register Customer
        $this->registerCustomer($customer, $request->sendmail);

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
        if ($request->GuardarEn)
          $z = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), $request->GuardarEn."\\FES-{$resolution->next_consecutive}", $batch);
        else
          $z = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), StorageService::tempPath("public/{$company->identification_number}/FES-{$resolution->next_consecutive}"), $batch);

        $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $signInvoice->ConsultarCUFE(), "INVOICE", $withHoldingTaxTotal, $notes);

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
        $invoice_doc->total_discount = $legalMonetaryTotals->allowance_total_amount;
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
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FES-{$resolution->next_consecutive}.xml")),
                'zipinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\{$batch}.zip")),
                'unsignedinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FE-{$resolution->next_consecutive}.xml")),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'cufe' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $QRStr
            ];
        }
        else{
            StorageService::uploadBatchIfS3([
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.pdf",
            ]);
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml"),
                'zipinvoicexml'=>base64_encode(file_get_contents(StorageService::tempPath("zip/{$company->id}/{$batch}.zip"))),
                'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml"),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'cufe' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $QRStr
            ];
        }
    }

    /**
     * Add Invoice AIU to batch.
     *
     * @param \App\Http\Requests\Api\InvoiceAIURequest $request
     * @param string                                   $batch
     *
     * @return \Illuminate\Http\Response
     */
    public function addinvoiceaiu(InvoiceAIURequest $request, $batch)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        // Actualizar Tablas

        $this->ActualizarTablas();

        //Document
        $invoice_doc = new Document();
        $invoice_doc->request_api = json_encode($request->all());
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
//        $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Customer
        $customerAll = collect($request->customer);
        if(isset($customerAll['municipality_id_fact']))
            $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
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
            $request->type_operation_id = 9;
        $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

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

        // Objeto contrato AIU
        $noteAIU = $request->noteAIU;

        // Payment form default
        $paymentFormAll = (object) array_merge($this->paymentFormDefault, $request->payment_form ?? []);
        $paymentForm = PaymentForm::findOrFail($paymentFormAll->payment_form_id);
        $paymentForm->payment_method_code = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->code;
        $paymentForm->nameMethod = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->name;
        $paymentForm->payment_due_date = $paymentFormAll->payment_due_date ?? null;
        $paymentForm->duration_measure = $paymentFormAll->duration_measure ?? null;

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
        foreach($request->with_holding_tax_total ?? [] as $item) {
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
        $invoice = $this->createXML(compact('user', 'company', 'customer', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'invoiceLines', 'allowanceCharges', 'legalMonetaryTotals', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'noteAIU', 'prepaidpayment', 'prepaidpayments', 'delivery', 'deliveryparty', 'request'));

        // Register Customer
        $this->registerCustomer($customer, $request->sendmail);

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

        if ($request->GuardarEn)
          $z = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), $request->GuardarEn."\\FES-{$resolution->next_consecutive}", $batch);
        else
          $z = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), StorageService::tempPath("public/{$company->identification_number}/FES-{$resolution->next_consecutive}"), $batch);

        $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $signInvoice->ConsultarCUFE(), "INVOICE", $withHoldingTaxTotal, $notes);

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
        $invoice_doc->total_discount = $legalMonetaryTotals->allowance_total_amount;
        $invoice_doc->taxes =  $request->tax_totals;
        $invoice_doc->total_tax = $legalMonetaryTotals->tax_inclusive_amount - $legalMonetaryTotals->tax_exclusive_amount;
        $invoice_doc->subtotal = $legalMonetaryTotals->line_extension_amount;
        $invoice_doc->total = $legalMonetaryTotals->payable_amount;
        $invoice_doc->version_ubl_id = 2;
        $invoice_doc->ambient_id = $company->type_environment_id;
        $invoice_doc->identification_number = $company->identification_number;
        $invoice_doc->save();

        if ($request->GuardarEn)
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FES-{$resolution->next_consecutive}.xml")),
                'zipinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\{$batch}.zip")),
                'unsignedinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FE-{$resolution->next_consecutive}.xml")),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'cufe' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $QRStr
            ];
        else{
            StorageService::uploadBatchIfS3([
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.pdf",
            ]);
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml"),
                'zipinvoicexml'=>base64_encode(file_get_contents(StorageService::tempPath("zip/{$company->id}/{$batch}.zip"))),
                'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml"),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'cufe' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $QRStr
            ];
        }
    }

    /**
     * Add Invoice mandate to batch.
     *
     * @param \App\Http\Requests\Api\InvoiceMandateRequest $request
     * @param string                                       $batch
     *
     * @return \Illuminate\Http\Response
     */
    public function addinvoicemandate(InvoiceMandateRequest $request, $batch)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        // Actualizar Tablas

        $this->ActualizarTablas();

        //Document
        $invoice_doc = new Document();
        $invoice_doc->request_api = json_encode($request->all());
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
//        $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Customer
        $customerAll = collect($request->customer);
        if(isset($customerAll['municipality_id_fact']))
            $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
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
          $request->type_operation_id = 11;
        $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

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

        // Payment form default
        $paymentFormAll = (object) array_merge($this->paymentFormDefault, $request->payment_form ?? []);
        $paymentForm = PaymentForm::findOrFail($paymentFormAll->payment_form_id);
        $paymentForm->payment_method_code = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->code;
        $paymentForm->nameMethod = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->name;
        $paymentForm->payment_due_date = $paymentFormAll->payment_due_date ?? null;
        $paymentForm->duration_measure = $paymentFormAll->duration_measure ?? null;

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
        foreach($request->with_holding_tax_total ?? [] as $item) {
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
        $invoice = $this->createXML(compact('user', 'company', 'customer', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'invoiceLines', 'allowanceCharges', 'legalMonetaryTotals', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'prepaidpayment', 'prepaidpayments', 'delivery', 'deliveryparty', 'request'));

        // Register Customer
        $this->registerCustomer($customer, $request->sendmail);

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

        if ($request->GuardarEn)
          $z = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), $request->GuardarEn."\\FES-{$resolution->next_consecutive}", $batch);
        else
          $z = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), StorageService::tempPath("public/{$company->identification_number}/FES-{$resolution->next_consecutive}"), $batch);

        $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $signInvoice->ConsultarCUFE(), "INVOICE", $withHoldingTaxTotal, $notes);

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
        $invoice_doc->total_discount = $legalMonetaryTotals->allowance_total_amount;
        $invoice_doc->taxes =  $request->tax_totals;
        $invoice_doc->total_tax = $legalMonetaryTotals->tax_inclusive_amount - $legalMonetaryTotals->tax_exclusive_amount;
        $invoice_doc->subtotal = $legalMonetaryTotals->line_extension_amount;
        $invoice_doc->total = $legalMonetaryTotals->payable_amount;
        $invoice_doc->version_ubl_id = 2;
        $invoice_doc->ambient_id = $company->type_environment_id;
        $invoice_doc->identification_number = $company->identification_number;
        $invoice_doc->save();

        if ($request->GuardarEn)
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FES-{$resolution->next_consecutive}.xml")),
                'zipinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\{$batch}.zip")),
                'unsignedinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FE-{$resolution->next_consecutive}.xml")),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'cufe' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $QRStr
            ];
        else{
            StorageService::uploadBatchIfS3([
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.pdf",
            ]);
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml"),
                'zipinvoicexml'=>base64_encode(file_get_contents(StorageService::tempPath("zip/{$company->id}/{$batch}.zip"))),
                'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml"),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'cufe' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $QRStr
            ];
        }
    }

    /**
     * Add Invoice Export to batch.
     *
     * @param \App\Http\Requests\Api\InvoiceExportRequest $request
     * @param string                                      $batch
     *
     * @return \Illuminate\Http\Response
     */
    public function addinvoiceexport(InvoiceExportRequest $request, $batch)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        // Actualizar Tablas

        $this->ActualizarTablas();

        //Document
        $invoice_doc = new Document();
        $invoice_doc->request_api = json_encode($request->all());
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
//        $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Customer
        $customerAll = collect($request->customer);
        if(isset($customerAll['municipality_id_fact']))
            $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
        $customer = new User($customerAll->toArray());

        // Customer company
        $customer->company = new Company($customerAll->toArray());

        // Delivery Terms

        $deliverytermsAll = collect($request->deliveryterms);
        $deliveryterms = (object)$deliverytermsAll->toArray();

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
        $idcurrency = TypeCurrency::findOrFail($request->idcurrency);

        // Calculation rate
        $calculationrate = $request->calculationrate;
        $calculationratedate = $request->calculationratedate;

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

        // Payment form default
        $paymentFormAll = (object) array_merge($this->paymentFormDefault, $request->payment_form ?? []);
        $paymentForm = PaymentForm::findOrFail($paymentFormAll->payment_form_id);
        $paymentForm->payment_method_code = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->code;
        $paymentForm->nameMethod = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->name;
        $paymentForm->payment_due_date = $paymentFormAll->payment_due_date ?? null;
        $paymentForm->duration_measure = $paymentFormAll->duration_measure ?? null;

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
        foreach($request->with_holding_tax_total ?? [] as $item) {
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
        $invoice = $this->createXML(compact('user', 'company', 'customer', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'invoiceLines', 'allowanceCharges', 'legalMonetaryTotals', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'idcurrency', 'calculationrate', 'calculationratedate', 'prepaidpayment', 'prepaidpayments', 'deliveryterms', 'delivery', 'deliveryparty', 'request'));

        // Register Customer
        $this->registerCustomer($customer, $request->sendmail);

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
        if ($request->GuardarEn)
          $z = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), $request->GuardarEn."\\FES-{$resolution->next_consecutive}", $batch);
        else
          $z = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), StorageService::tempPath("public/{$company->identification_number}/FES-{$resolution->next_consecutive}"), $batch);

        $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $signInvoice->ConsultarCUFE(), "INVOICE", $withHoldingTaxTotal, $notes);

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
        $invoice_doc->total_discount = $legalMonetaryTotals->allowance_total_amount;
        $invoice_doc->taxes =  $request->tax_totals;
        $invoice_doc->total_tax = $legalMonetaryTotals->tax_inclusive_amount - $legalMonetaryTotals->tax_exclusive_amount;
        $invoice_doc->subtotal = $legalMonetaryTotals->line_extension_amount;
        $invoice_doc->total = $legalMonetaryTotals->payable_amount;
        $invoice_doc->version_ubl_id = 2;
        $invoice_doc->ambient_id = $company->type_environment_id;
        $invoice_doc->identification_number = $company->identification_number;
        $invoice_doc->save();

        if ($request->GuardarEn)
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FES-{$resolution->next_consecutive}.xml")),
                'zipinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\{$batch}.zip")),
                'unsignedinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FE-{$resolution->next_consecutive}.xml")),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'cufe' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $QRStr
            ];
        else{
            StorageService::uploadBatchIfS3([
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.pdf",
            ]);
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml"),
                'zipinvoicexml'=>base64_encode(file_get_contents(StorageService::tempPath("zip/{$company->id}/{$batch}.zip"))),
                'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml"),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'cufe' => $signInvoice->ConsultarCUFE(),
                'QRStr' => $QRStr
            ];
        }
    }

    /**
     * Add Invoice Contingency to batch.
     *
     * @param \App\Http\Requests\Api\InvoiceContingencyRequest $request
     * @param string                                           $batch
     *
     * @return \Illuminate\Http\Response
     */
    public function addinvoicecontingency(InvoiceContingencyRequest $request, $batch)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        // Actualizar Tablas

        $this->ActualizarTablas();

        //Document
        $invoice_doc = new Document();
        $invoice_doc->request_api = json_encode($request->all());
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
//        $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Customer
        $customerAll = collect($request->customer);
        if(isset($customerAll['municipality_id_fact']))
            $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
        $customer = new User($customerAll->toArray());

        // Customer company
        $customer->company = new Company($customerAll->toArray());

        // Delivery
        $deliveryAll = collect($request->delivery);
        $delivery = new User($deliveryAll->toArray());

        // Delivery company
        $delivery->company = new Company($deliveryAll->toArray());

        // Delivery party
        $deliverypartyAll = collect($request->deliveryparty);
        $deliveryparty = new User($deliverypartyAll->toArray());

        // Delivery party company
        $deliveryparty->company = new Company($deliverypartyAll->toArray());

        // Type operation id
        if(!$request->type_operation_id)
            $request->type_operation_id = 10;
        $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

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

        // Additional document reference
        $AdditionalDocumentReferenceID = $request->AdditionalDocumentReferenceID;
        $AdditionalDocumentReferenceDate = $request->AdditionalDocumentReferenceDate;
        $AdditionalDocumentReferenceTypeDocument = $request->AdditionalDocumentReferenceTypeDocument;

        // Payment form default
        $paymentFormAll = (object) array_merge($this->paymentFormDefault, $request->payment_form ?? []);
        $paymentForm = PaymentForm::findOrFail($paymentFormAll->payment_form_id);
        $paymentForm->payment_method_code = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->code;
        $paymentForm->nameMethod = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->name;
        $paymentForm->payment_due_date = $paymentFormAll->payment_due_date ?? null;
        $paymentForm->duration_measure = $paymentFormAll->duration_measure ?? null;

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
        foreach($request->with_holding_tax_total ?? [] as $item) {
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
        $invoice = $this->createXML(compact('user', 'company', 'customer', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'invoiceLines', 'allowanceCharges', 'legalMonetaryTotals', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'AdditionalDocumentReferenceID', 'AdditionalDocumentReferenceDate', 'AdditionalDocumentReferenceTypeDocument', 'prepaidpayment', 'prepaidpayments', 'delivery', 'deliveryparty', 'request'));

        // Register Customer
        $this->registerCustomer($customer, $request->sendmail);

        // Signature XML
        $signInvoice = new SignInvoice($company->certificate->path, $company->certificate->password);
        $signInvoice->softwareID = $company->software->identifier;
        $signInvoice->pin = $company->software->pin;

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

        if ($request->GuardarEn)
          $z = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), $request->GuardarEn."\\FES-{$resolution->next_consecutive}", $batch);
        else
          $z = $this->zipBase64($company, $resolution, $signInvoice->sign($invoice), StorageService::tempPath("public/{$company->identification_number}/FES-{$resolution->next_consecutive}"), $batch);

        $invoice_doc->prefix = $resolution->prefix;
        $invoice_doc->customer = $customer->company->identification_number;
        $invoice_doc->xml = "FES-{$resolution->next_consecutive}.xml";
        $invoice_doc->client_id = $customer->company->identification_number;
        $invoice_doc->client =  $request->customer ;
        if(property_exists($request, 'id_currency'))
            $invoice_doc->currency_id = $request->id_currency;
        else
            $invoice_doc->currency_id = 35;
        $invoice_doc->date_issue = date("Y-m-d H:i:s");
        $invoice_doc->sale = $legalMonetaryTotals->payable_amount;
        $invoice_doc->total_discount = $legalMonetaryTotals->allowance_total_amount;
        $invoice_doc->taxes =  $request->tax_totals;
        $invoice_doc->total_tax = $legalMonetaryTotals->tax_inclusive_amount - $legalMonetaryTotals->tax_exclusive_amount;
        $invoice_doc->subtotal = $legalMonetaryTotals->line_extension_amount;
        $invoice_doc->total = $legalMonetaryTotals->payable_amount;
        $invoice_doc->version_ubl_id = 2;
        $invoice_doc->ambient_id = $company->type_environment_id;
        $invoice_doc->identification_number = $company->identification_number;
        $invoice_doc->save();

        if ($request->GuardarEn)
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FES-{$resolution->next_consecutive}.xml")),
                'zipinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\{$batch}.zip")),
                'unsignedinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\FE-{$resolution->next_consecutive}.xml")),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'cude' => $signInvoice->ConsultarCUDE()
            ];
        else{
            StorageService::uploadBatchIfS3([
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/FES-{$resolution->next_consecutive}.pdf",
            ]);
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FES-{$resolution->next_consecutive}.xml"),
                'zipinvoicexml'=>base64_encode(file_get_contents(StorageService::tempPath("zip/{$company->id}/{$batch}.zip"))),
                'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/FE-{$resolution->next_consecutive}.xml"),
                'urlinvoicexml'=>"FES-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"FES-{$resolution->next_consecutive}.pdf",
                'cude' => $signInvoice->ConsultarCUDE()
            ];
        }
        }

    /**
     * Add Credit Note to batch.
     *
     * @param \App\Http\Requests\Api\CreditNoteRequest $request
     * @param string                                   $batch
     *
     * @return \Illuminate\Http\Response
     */
    public function addcreditnote(CreditNoteRequest $request, $batch)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        // Actualizar Tablas

        $this->ActualizarTablas();

        //Document
        $invoice_doc = new Document();
        $invoice_doc->request_api = json_encode($request->all());
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
//        $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Customer
        $customerAll = collect($request->customer);
        if(isset($customerAll['municipality_id_fact']))
            $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
        $customer = new User($customerAll->toArray());

        // Customer company
        $customer->company = new Company($customerAll->toArray());

        // Type operation id
        if(!$request->type_operation_id)
            $request->type_operation_id = 12;
        $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

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

        // Discrepancy response
        $discrepancycode = $request->discrepancyresponsecode;
        $discrepancydescription = $request->discrepancyresponsedescription;

        // Payment form default
        $paymentFormAll = (object) array_merge($this->paymentFormDefault, $request->payment_form ?? []);
        $paymentForm = PaymentForm::findOrFail($paymentFormAll->payment_form_id);
        $paymentForm->payment_method_code = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->code;
        $paymentForm->payment_due_date = $paymentFormAll->payment_due_date ?? null;
        $paymentForm->duration_measure = $paymentFormAll->duration_measure ?? null;

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
        foreach($request->with_holding_tax_total ?? [] as $item) {
            $withHoldingTaxTotal->push(new TaxTotal($item));
        }

        // Legal monetary totals
        $legalMonetaryTotals = new LegalMonetaryTotal($request->legal_monetary_totals);

        // Credit note lines
        $creditNoteLines = collect();
        foreach ($request->credit_note_lines as $creditNoteLine) {
            $creditNoteLines->push(new CreditNoteLine($creditNoteLine));
        }

        // Billing reference
        $billingReference = new BillingReference($request->billing_reference);

        // Create XML
        $crediNote = $this->createXML(compact('user', 'company', 'customer', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'creditNoteLines', 'allowanceCharges', 'legalMonetaryTotals', 'billingReference', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'discrepancycode', 'discrepancydescription', 'request'));

        // Register Customer
        $this->registerCustomer($customer, $request->sendmail);

        // Signature XML
        $signCreditNote = new SignCreditNote($company->certificate->path, $company->certificate->password);
        $signCreditNote->softwareID = $company->software->identifier;
        $signCreditNote->pin = $company->software->pin;

        if ($request->GuardarEn){
            if (!is_dir($request->GuardarEn)) {
                mkdir($request->GuardarEn);
            }
            $signCreditNote->GuardarEn = $request->GuardarEn."\\NC-{$resolution->next_consecutive}.xml";
        }
        else{
            StorageService::ensureDirectory("public/{$company->identification_number}");
            $signCreditNote->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/NC-{$resolution->next_consecutive}.xml");
        }

        if ($request->GuardarEn)
          $z = $this->zipBase64($company, $resolution, $signCreditNote->sign($crediNote), $request->GuardarEn."\\NCS-{$resolution->next_consecutive}", $batch);
        else
          $z = $this->zipBase64($company, $resolution, $signCreditNote->sign($crediNote), StorageService::tempPath("public/{$company->identification_number}/NCS-{$resolution->next_consecutive}"), $batch);

        $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $signCreditNote->ConsultarCUDE(), "NC", $withHoldingTaxTotal, $notes);

        $invoice_doc->prefix = $resolution->prefix;
        $invoice_doc->customer = $customer->company->identification_number;
        $invoice_doc->xml = "NCS-{$resolution->next_consecutive}.xml";
        $invoice_doc->pdf = "NCS-{$resolution->next_consecutive}.pdf";
        $invoice_doc->client_id = $customer->company->identification_number;
        $invoice_doc->client =  $request->customer ;
        if(property_exists($request, 'id_currency'))
            $invoice_doc->currency_id = $request->id_currency;
        else
            $invoice_doc->currency_id = 35;
        $invoice_doc->date_issue = date("Y-m-d H:i:s");
        $invoice_doc->sale = $legalMonetaryTotals->payable_amount;
        $invoice_doc->total_discount = $legalMonetaryTotals->allowance_total_amount;
        $invoice_doc->taxes =  $request->tax_totals;
        $invoice_doc->total_tax = $legalMonetaryTotals->tax_inclusive_amount - $legalMonetaryTotals->tax_exclusive_amount;
        $invoice_doc->subtotal = $legalMonetaryTotals->line_extension_amount;
        $invoice_doc->total = $legalMonetaryTotals->payable_amount;
        $invoice_doc->version_ubl_id = 2;
        $invoice_doc->ambient_id = $company->type_environment_id;
        $invoice_doc->identification_number = $company->identification_number;
        $invoice_doc->save();

        if ($request->GuardarEn)
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\NCS-{$resolution->next_consecutive}.xml")),
                'zipinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\{$batch}.zip")),
                'unsignedinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\NC-{$resolution->next_consecutive}.xml")),
                'urlinvoicexml'=>"NCS-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"NCS-{$resolution->next_consecutive}.pdf",
                'cude' => $signCreditNote->ConsultarCUDE(),
                'QRStr' => $QRStr
            ];
        else{
            StorageService::uploadBatchIfS3([
                "public/{$company->identification_number}/NCS-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/NC-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/NCS-{$resolution->next_consecutive}.pdf",
            ]);
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/NCS-{$resolution->next_consecutive}.xml"),
                'zipinvoicexml'=>base64_encode(file_get_contents(StorageService::tempPath("zip/{$company->id}/{$batch}.zip"))),
                'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/NC-{$resolution->next_consecutive}.xml"),
                'urlinvoicexml'=>"NCS-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"NCS-{$resolution->next_consecutive}.pdf",
                'cude' => $signCreditNote->ConsultarCUDE(),
                'QRStr' => $QRStr
            ];
        }
    }

    /**
     * Add Debit Note to batch.
     *
     * @param \App\Http\Requests\Api\DebitNoteRequest  $request
     * @param string                                   $batch
     *
     * @return \Illuminate\Http\Response
     */
    public function adddebitnote(DebitNoteRequest $request, $batch)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        // Actualizar Tablas

        $this->ActualizarTablas();

        //Document
        $invoice_doc = new Document();
        $invoice_doc->request_api = json_encode($request->all());
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
//        $invoice_doc->save();

        // Type document
        $typeDocument = TypeDocument::findOrFail($request->type_document_id);

        // Customer
        $customerAll = collect($request->customer);
        if(isset($customerAll['municipality_id_fact']))
            $customerAll['municipality_id'] = Municipality::where('codefacturador', $customerAll['municipality_id_fact'])->first();
        $customer = new User($customerAll->toArray());

        // Customer company
        $customer->company = new Company($customerAll->toArray());

        // Type operation id
        if(!$request->type_operation_id)
            $request->type_operation_id = 6;
        $typeoperation = TypeOperation::findOrFail($request->type_operation_id);

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

        // Discrepancy response
        $discrepancycode = $request->discrepancyresponsecode;
        $discrepancydescription = $request->discrepancyresponsedescription;

        // Payment form default
        $paymentFormAll = (object) array_merge($this->paymentFormDefault, $request->payment_form ?? []);
        $paymentForm = PaymentForm::findOrFail($paymentFormAll->payment_form_id);
        $paymentForm->payment_method_code = PaymentMethod::findOrFail($paymentFormAll->payment_method_id)->code;
        $paymentForm->payment_due_date = $paymentFormAll->payment_due_date ?? null;
        $paymentForm->duration_measure = $paymentFormAll->duration_measure ?? null;

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
        foreach($request->with_holding_tax_total ?? [] as $item) {
            $withHoldingTaxTotal->push(new TaxTotal($item));
        }

        // Requested monetary totals
        $requestedMonetaryTotals = new LegalMonetaryTotal($request->requested_monetary_totals);

        // Debit note lines
        $debitNoteLines = collect();
        foreach ($request->debit_note_lines as $debitNoteLine) {
            $debitNoteLines->push(new DebitNoteLine($debitNoteLine));
        }

        // Billing reference
        $billingReference = new BillingReference($request->billing_reference);

        // Create XML
        $debitNote = $this->createXML(compact('user', 'company', 'customer', 'taxTotals', 'withHoldingTaxTotal', 'resolution', 'paymentForm', 'typeDocument', 'debitNoteLines', 'allowanceCharges', 'requestedMonetaryTotals', 'billingReference', 'date', 'time', 'notes', 'typeoperation', 'orderreference', 'discrepancycode', 'discrepancydescription', 'request'));

        // Register Customer
        $this->registerCustomer($customer, $request->sendmail);

        // Signature XML
        $signDebitNote = new SignDebitNote($company->certificate->path, $company->certificate->password);
        $signDebitNote->softwareID = $company->software->identifier;
        $signDebitNote->pin = $company->software->pin;

        if ($request->GuardarEn){
            if (!is_dir($request->GuardarEn)) {
                mkdir($request->GuardarEn);
            }
            $signDebitNote->GuardarEn = $request->GuardarEn."\\ND-{$resolution->next_consecutive}.xml";
        }
        else{
            StorageService::ensureDirectory("public/{$company->identification_number}");
            $signDebitNote->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/ND-{$resolution->next_consecutive}.xml");
        }

        if ($request->GuardarEn)
          $z = $this->zipBase64($company, $resolution, $signDebitNote->sign($debitNote), $request->GuardarEn."\\NDS-{$resolution->next_consecutive}", $batch);
        else
          $z = $this->zipBase64($company, $resolution, $signDebitNote->sign($debitNote), StorageService::tempPath("public/{$company->identification_number}/NDS-{$resolution->next_consecutive}"), $batch);

        $QRStr = $this->createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $signDebitNote->ConsultarCUDE(), "ND", $withHoldingTaxTotal, $notes);

        $invoice_doc->prefix = $resolution->prefix;
        $invoice_doc->customer = $customer->company->identification_number;
        $invoice_doc->xml = "NDS-{$resolution->next_consecutive}.xml";
        $invoice_doc->pdf = "NDS-{$resolution->next_consecutive}.pdf";
        $invoice_doc->client_id = $customer->company->identification_number;
        $invoice_doc->client =  $request->customer ;
        if(property_exists($request, 'id_currency'))
            $invoice_doc->currency_id = $request->id_currency;
        else
            $invoice_doc->currency_id = 35;
        $invoice_doc->date_issue = date("Y-m-d H:i:s");
        $invoice_doc->sale = $requestedMonetaryTotals->payable_amount;
        $invoice_doc->total_discount = $requestedMonetaryTotals->allowance_total_amount;
        $invoice_doc->taxes =  $request->tax_totals;
        $invoice_doc->total_tax = $requestedMonetaryTotals->tax_inclusive_amount - $requestedMonetaryTotals->tax_exclusive_amount;
        $invoice_doc->subtotal = $requestedMonetaryTotals->line_extension_amount;
        $invoice_doc->total = $requestedMonetaryTotals->payable_amount;
        $invoice_doc->version_ubl_id = 2;
        $invoice_doc->ambient_id = $company->type_environment_id;
        $invoice_doc->identification_number = $company->identification_number;
        $invoice_doc->save();

        if ($request->GuardarEn)
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\NDS-{$resolution->next_consecutive}.xml")),
                'zipinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\{$batch}.zip")),
                'unsignedinvoicexml'=>base64_encode(file_get_contents($request->GuardarEn."\\ND-{$resolution->next_consecutive}.xml")),
                'urlinvoicexml'=>"NDS-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"NDS-{$resolution->next_consecutive}.pdf",
                'cude' => $signDebitNote->ConsultarCUDE(),
                'QRStr' => $QRStr
            ];
        else{
            StorageService::uploadBatchIfS3([
                "public/{$company->identification_number}/NDS-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/ND-{$resolution->next_consecutive}.xml",
                "public/{$company->identification_number}/NDS-{$resolution->next_consecutive}.pdf",
            ]);
            return [
                'message' => "{$typeDocument->name} #{$resolution->next_consecutive} generada y agregada con éxito al batch {$batch}.zip",
                'invoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/NDS-{$resolution->next_consecutive}.xml"),
                'zipinvoicexml'=>base64_encode(file_get_contents(StorageService::tempPath("zip/{$company->id}/{$batch}.zip"))),
                'unsignedinvoicexml'=>StorageService::getBase64Auto("public/{$company->identification_number}/ND-{$resolution->next_consecutive}.xml"),
                'urlinvoicexml'=>"NDS-{$resolution->next_consecutive}.xml",
                'urlinvoicepdf'=>"NDS-{$resolution->next_consecutive}.pdf",
                'cude' => $signDebitNote->ConsultarCUDE(),
                'QRStr' => $QRStr
            ];
        }
    }

    /**
     * Send batch.
     *
     * @param string $batch
     *
     * @return \Illuminate\Http\Response
     */
    public function sendbatch($batch)
    {
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        $sendBillAsync = new SendBillAsync($company->certificate->path, $company->certificate->password);
        $sendBillAsync->To = $company->software->url;
        $sendBillAsync->fileName = "{$batch}.zip";

        $sendBillAsync->contentFile = base64_encode(file_get_contents(StorageService::tempPath("zip/{$company->id}/{$batch}.zip")));

        StorageService::ensureDirectory("public/{$company->identification_number}");
        $responseDian = $sendBillAsync->signToSend(StorageService::tempPath("public/{$company->identification_number}/ReqBATCH-{$batch}.xml"))->getResponseToObject(StorageService::tempPath("public/{$company->identification_number}/RptaBATCH-{$batch}.xml"));

        StorageService::uploadBatchIfS3([
            "public/{$company->identification_number}/ReqBATCH-{$batch}.xml",
            "public/{$company->identification_number}/RptaBATCH-{$batch}.xml",
        ]);

        return [
            'message' => "Batch {$batch}.zip enviado con éxito",
            'ResponseDian' => $responseDian,
            'zipinvoicexml'=>base64_encode(file_get_contents(StorageService::tempPath("zip/{$company->id}/{$batch}.zip"))),
            'reqfe'=>StorageService::getBase64Auto("public/{$company->identification_number}/ReqBATCH-{$batch}.xml"),
            'rptafe'=>StorageService::getBase64Auto("public/{$company->identification_number}/RptaBATCH-{$batch}.xml"),
        ];
    }
}
