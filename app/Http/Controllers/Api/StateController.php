<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use ubl21dian\Templates\SOAP\GetStatus;
use ubl21dian\Templates\SOAP\GetStatusZip;
use ubl21dian\Templates\SOAP\GetStatusEvents;
use App\Traits\DocumentTrait;
use App\TypeDocument;
use App\Mail\InvoiceMail;
use App\Customer;
use App\Document;
use App\TypeDocumentIdentification;
use App\Resolution;
use App\User;
use App\Company;
use App\TypeLiability;
use ubl21dian\XAdES\SignAttachedDocument;
use App\Http\Requests\Api\StatusRequest;
use App\Http\Requests\Api\XmlDocumentRequest;
use Illuminate\Support\Facades\Mail;
use App\Services\StorageService;

class StateController extends Controller
{
    use DocumentTrait;

    /**
     * Zip.
     *
     * @param string $trackId
     *
     * @return array
     */

    public function zip(StatusRequest $request, $trackId, $GuardarEn = false)
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

        if($request->is_payroll || $request->is_eqdoc)
            return [
                'success' => false,
                'message' => "Nomina y Documentos Equivalentes no estan disponibles en la version Community."
            ];

        $getStatusZip = new GetStatusZip($user->company->certificate->path, $user->company->certificate->password, $user->company->software->url);

        $getStatusZip->trackId = $trackId;
        $GuardarEn = str_replace("_", "\\", $GuardarEn);

        if ($GuardarEn){
            if (!is_dir($GuardarEn)) {
                mkdir($GuardarEn);
            }
        }
        else{
            StorageService::ensureDirectory("public/{$company->identification_number}");
        }

        $respuestadian = '';
        $typeDocument = TypeDocument::findOrFail(7);
        $resolution = NULL;
        $customer = NULL;
        $filename = '';
        //        $xml = new \DOMDocument;
        $ar = new \DOMDocument;
        if ($GuardarEn){
            try{
                $respuestadian = $getStatusZip->signToSend($GuardarEn."\\ReqZIP-".$trackId.".xml")->getResponseToObject($GuardarEn."\\RptaZIP-".$trackId.".xml");
                if(isset($respuestadian->html))
                    return [
                        'success' => false,
                        'message' => "El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde..."
                    ];

                if($respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->IsValid == 'true'){
                    if(isset($respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlFileName->_attributes))
                    {
                        $invoicenumber = $this->InvoiceByZipKey($company->identification_number, $trackId);
                        $signedxml = StorageService::getAutoLocal("public/{$company->identification_number}/FES-".$invoicenumber);
                    }
                    else
                        $signedxml = StorageService::getAutoLocal("xml/{$company->id}/".$respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlFileName.".xml");

                    if(strpos($signedxml, "</Invoice>") > 0)
                        $td = '/Invoice';
                    else
                        if(strpos($signedxml, "</CreditNote>") > 0)
                            $td = '/CreditNote';
                        else
                            if(strpos($signedxml, "</DebitNote>") > 0)
                                $td = '/DebitNote';
                            else
                                if(strpos($signedxml, "</NominaIndividual>") > 0)
                                    $td = '/NominaIndividual';
                                else
                                    if(strpos($signedxml, "</NominaIndividualDeAjuste>") > 0)
                                        $td = '/NominaIndividualDeAjuste';

                    //  $xml->loadXML($signedxml);

                    $filename = str_replace('ttr', 'ad', str_replace('pos', 'ad', str_replace('ads', 'ad', str_replace('dse', 'ad', str_replace('ni', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlFileName))))))));
                    if($request->atacheddocument_name_prefix)
                        $filename = $request->atacheddocument_name_prefix.$filename;

                    $cufecude = $respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlDocumentKey;

                    //  if ($td == '/NominaIndividual' || $td == 'NominaIndividualDeAjuste')
                    //      $cufecude = $this->getTag($signedxml, 'InformacionGeneral', 0, 'CUNE');
                    //  else
                    //      $cufecude = $this->ValueXML($signedxml, $td."/cbc:UUID/");
                    $appresponsexml = base64_decode($respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlBase64Bytes);
                    $ar->loadXML($appresponsexml);
                    $fechavalidacion = $ar->documentElement->getElementsByTagName('IssueDate')->item(0)->nodeValue;
                    $horavalidacion = $ar->documentElement->getElementsByTagName('IssueTime')->item(0)->nodeValue;
                    if($td == '/NominaIndividual' || $td == '/NominaIndividualDeAjuste')
                        $document_number = $this->getTag($signedxml, 'NumeroSecuenciaXML', 0, 'Numero');
                    else
                        $document_number = $this->ValueXML($signedxml, $td."/cbc:ID/");

                    if($td == '/Invoice')
                        if(isset($this->getTag($signedxml, 'Prefix', 0)->nodeValue))
                            $resolution = Resolution::where('prefix', $this->getTag($signedxml, 'Prefix', 0)->nodeValue)->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                        else{
                            if($this->getTag($signedxml, 'InvoiceTypeCode', 0)->nodeValue == 35)
                                $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->firstOrFail();
                            else
                                $resolution = Resolution::where('prefix', NULL)->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                        }
                    //      $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                    else
                        if($td == '/NominaIndividual' || $td == '/NominaIndividualDeAjuste')
                            $resolution = Resolution::where('prefix', $this->getTag($signedxml, 'NumeroSecuenciaXML', 0, 'Prefijo'))->firstOrFail();
                        else
                            $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->firstOrFail();

                    $resolution->document_number = $document_number;
                    // Create XML AttachedDocument
                    $at = '';
                    if($td != '/NominaIndividual' && $td != '/NominaIndividualDeAjuste'){
                        if($this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: documento soporte en adquisiciones efectuadas a no obligados a facturar.' && $this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: Nota de ajuste al documento soporte en adquisiciones efectuadas a sujetos no obligados a expedir factura o documento equivalente')
                            $u = ['name' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name/"),
                                  'email' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'identification_number' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"),
                                  'phone' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'address' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cac:RegistrationAddress/cac:AddressLine/cbc:Line/"),
                                  'type_liability_id' => TypeLiability::where('code', $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:TaxLevelCode/"))->firstOrFail()->id,
                                  'type_regime_id' => "2",
                                  'type_document_identification_id' => TypeDocumentIdentification::where('code', 'Like', '%'.$this->getTag($signedxml, "CompanyID", 2, "schemeName").'%')->firstOrFail()->id,
                                  'dv' => $this->getTag($signedxml, "CompanyID", 2, "schemeID"),
                                 ];
                        else
                            $u = ['name' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name/"),
                                  'email' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'identification_number' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"),
                                  'phone' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'address' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cac:RegistrationAddress/cac:AddressLine/cbc:Line/"),
                                  'type_liability_id' => TypeLiability::where('code', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:TaxLevelCode/"))->firstOrFail()->id,
                                  'type_regime_id' => "2",
                                  'type_document_identification_id' => TypeDocumentIdentification::where('code', 'Like', '%'.$this->getTag($signedxml, "CompanyID", 1, "schemeName").'%')->firstOrFail()->id,
                                  'dv' => $this->getTag($signedxml, "CompanyID", 1, "schemeID"),
                                 ];

                        $customer = new user($u);
                        $customer->company = new Company($u);
                        $attacheddocument = $this->createXML(compact('user', 'company', 'customer', 'resolution', 'typeDocument', 'cufecude', 'signedxml', 'appresponsexml', 'fechavalidacion', 'horavalidacion', 'document_number'));

                        // Signature XML
                        $signAttachedDocument = new SignAttachedDocument($company->certificate->path, $company->certificate->password);
                        $signAttachedDocument->GuardarEn = $GuardarEn."\\{$filename}.xml";

                        $at = $signAttachedDocument->sign($attacheddocument)->xml;
                        //      $at = str_replace("&gt;", ">", str_replace("&quot;", '"', str_replace("&lt;", "<", $at)));
                        //      $file = fopen($GuardarEn."\\Attachment-".$this->valueXML($signedxml, $td."/cbc:ID/").".xml", "w");
                        $file = fopen($GuardarEn."\\{$filename}".".xml", "w");
                        fwrite($file, $at);
                        fclose($file);
                        if($this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: documento soporte en adquisiciones efectuadas a no obligados a facturar.' && $this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: Nota de ajuste al documento soporte en adquisiciones efectuadas a sujetos no obligados a expedir factura o documento equivalente')
                            $customer = Customer::findOrFail($this->valueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"));
                        else
                            $customer = Customer::findOrFail($this->valueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/"));
                        $invoice = Document::where('identification_number', '=', $company->identification_number)
                                           ->where('customer', '=', $customer->identification_number)
                                           ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                           ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                           ->where('state_document_id', '=', 0)->get();
                        if(count($invoice) > 0){
                            $invoice[0]->state_document_id = 1;
                            $invoice[0]->cufe = $cufecude;
                            $invoice[0]->save();
                        }
                        if(isset($request))
                            if($request->sendmail){
                                $invoice = Document::where('identification_number', '=', $company->identification_number)
                                                   ->where('customer', '=', $customer->identification_number)
                                                   ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                                   ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                                   ->where('state_document_id', '=', 1)->get();
                                if(count($invoice) > 0 && $customer->identification_number != '222222222222'){
                                    try{
                                        Mail::to($customer->email)->send(new InvoiceMail($invoice, $customer, $company, $GuardarEn, FALSE, FALSE, $filename));
                                        if($request->senmailtome)
                                            Mail::to($user->email)->send(new InvoiceMail($invoice, $customer, $company, $GuardarEn, FALSE, FALSE, $filename));
                                        if($request->email_cc_list){
                                            foreach($request->email_cc_list as $email)
                                                Mail::to($email)->send(new InvoiceMail($invoice, $customer, $company, $GuardarEn, FALSE, FALSE, $filename));
                                        }
                                        $invoice[0]->send_email_success = 1;
                                        $invoice[0]->save();
                                    } catch (\Exception $m) {
                                        \Log::debug($m->getMessage());
                                    }
                                }
                            }
                    }
                }
                else
                  $at = '';
            } catch (\Exception $e) {
                return $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian));
            }
            return [
                'message' => 'Consulta generada con éxito',
                'ResponseDian' => $respuestadian,
                'reqzip'=>base64_encode(file_get_contents($GuardarEn."\\ReqZIP-{$trackId}.xml")),
                'rptazip'=>base64_encode(file_get_contents($GuardarEn."\\RptaZIP-{$trackId}.xml")),
                'attacheddocument'=>base64_encode($at),
                'cufecude'=>$cufecude,
                'certificate_days_left' => $certificate_days_left,
            ];
        }
        else{
            try{
                $respuestadian = $getStatusZip->signToSend(StorageService::tempPath("public/{$company->identification_number}/ReqZIP-".$trackId.".xml"))->getResponseToObject(StorageService::tempPath("public/{$company->identification_number}/RptaZIP-".$trackId.".xml"));
                if(isset($respuestadian->html))
                    return [
                        'success' => false,
                        'message' => "El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde..."
                    ];

                if($respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->IsValid == 'true'){
                    if(isset($respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlFileName->_attributes))
                    {
                        $invoicenumber = $this->InvoiceByZipKey($company->identification_number, $trackId);
                        $signedxml = StorageService::getAutoLocal("public/{$company->identification_number}/FES-".$invoicenumber);
                    }
                    else{
                        $signedxml = StorageService::getAutoLocal("xml/{$company->id}/".$respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlFileName.".xml");
                    }
                    //  $xml->loadXML($signedxml);
                    if(strpos($signedxml, "</Invoice>") > 0)
                        $td = '/Invoice';
                    else
                        if(strpos($signedxml, "</CreditNote>") > 0)
                            $td = '/CreditNote';
                        else
                            if(strpos($signedxml, "</DebitNote>") > 0)
                                $td = '/DebitNote';
                            else
                                if(strpos($signedxml, "</NominaIndividual>") > 0)
                                    $td = '/NominaIndividual';
                                else
                                    if(strpos($signedxml, "</NominaIndividualDeAjuste>") > 0)
                                        $td = '/NominaIndividualDeAjuste';

                    //  if(isset($respuestadian->Envelope->Body->GetStatusZip;Response->GetStatusZipResult->DianResponse->XmlFileName->_attributes))
                    //      $xml = $this->readXML(storage_path("app/public/{$company->identification_number}/FES-".$invoicenumber));
                    //  else
                    //      $xml = $this->readXML(storage_path("app/xml/{$company->id}/".$respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlFileName.".xml"));

                    //  return $this->ValueXML($signedxml, '/CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:Name/');

                    $filename = str_replace('ttr', 'ad', str_replace('pos', 'ad', str_replace('ads', 'ad', str_replace('dse', 'ad', str_replace('ni', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlFileName))))))));
                    if($request->atacheddocument_name_prefix)
                        $filename = $request->atacheddocument_name_prefix.$filename;

                    $cufecude = $respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlDocumentKey;
                    //  if($td == '/NominaIndividual' || $td == 'NominaIndividualDeAjuste')
                    //      $cufecude = $this->getTag($signedxml, 'InformacionGeneral', 0, 'CUNE');
                    //  else
                    //      $cufecude = $this->ValueXML($signedxml, $td."/cbc:UUID/");

                    $appresponsexml = base64_decode($respuestadian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->XmlBase64Bytes);
                    $ar->loadXML($appresponsexml);
                    $fechavalidacion = $ar->documentElement->getElementsByTagName('IssueDate')->item(0)->nodeValue;
                    $horavalidacion = $ar->documentElement->getElementsByTagName('IssueTime')->item(0)->nodeValue;
                    if($td == '/NominaIndividual' || $td == '/NominaIndividualDeAjuste')
                        $document_number = $this->getTag($signedxml, 'NumeroSecuenciaXML', 0, 'Numero');
                    else
                        $document_number = $this->ValueXML($signedxml, $td."/cbc:ID/");

                    if($td == '/Invoice')
                        if(isset($this->getTag($signedxml, 'Prefix', 0)->nodeValue))
                            $resolution = Resolution::where('prefix', $this->getTag($signedxml, 'Prefix', 0)->nodeValue)->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                        else{
                            if($this->getTag($signedxml, 'InvoiceTypeCode', 0)->nodeValue == 35)
                                $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->firstOrFail();
                            else
                                $resolution = Resolution::where('prefix', NULL)->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                        }
                    //      $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                    else
                        if($td == '/NominaIndividual' || $td == '/NominaIndividualDeAjuste')
                            $resolution = Resolution::where('prefix', $this->getTag($signedxml, 'NumeroSecuenciaXML', 0, 'Prefijo'))->firstOrFail();
                        else
                            $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->firstOrFail();

                    $resolution->document_number = $document_number;
                      // Create XML AttachedDocument
                    $at = '';
                    if($td != '/NominaIndividual' && $td != '/NominaIndividualDeAjuste')
                    {
                        if($this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: documento soporte en adquisiciones efectuadas a no obligados a facturar.' && $this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: Nota de ajuste al documento soporte en adquisiciones efectuadas a sujetos no obligados a expedir factura o documento equivalente')
                            $u = ['name' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name/"),
                                  'email' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'identification_number' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"),
                                  'phone' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'address' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cac:RegistrationAddress/cac:AddressLine/cbc:Line/"),
                                  'type_liability_id' => TypeLiability::where('code', $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:TaxLevelCode/"))->firstOrFail()->id,
                                  'type_regime_id' => "2",
                                  'type_document_identification_id' => TypeDocumentIdentification::where('code', 'Like', '%'.$this->getTag($signedxml, "CompanyID", 2, "schemeName").'%')->firstOrFail()->id,
                                  'dv' => $this->getTag($signedxml, "CompanyID", 2, "schemeID"),
                                 ];
                        else
                            $u = ['name' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name/"),
                                  'email' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'identification_number' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"),
                                  'phone' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'address' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cac:RegistrationAddress/cac:AddressLine/cbc:Line/"),
                                  'type_liability_id' => TypeLiability::where('code', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:TaxLevelCode/"))->firstOrFail()->id,
                                  'type_regime_id' => "2",
                                  'type_document_identification_id' => TypeDocumentIdentification::where('code', 'Like', '%'.$this->getTag($signedxml, "CompanyID", 1, "schemeName").'%')->firstOrFail()->id,
                                  'dv' => $this->getTag($signedxml, "CompanyID", 1, "schemeID"),
                                 ];

                        $customer = new user($u);
                        $customer->company = new Company($u);
                        $attacheddocument = $this->createXML(compact('user', 'company', 'customer', 'resolution', 'typeDocument', 'cufecude', 'signedxml', 'appresponsexml', 'fechavalidacion', 'horavalidacion', 'document_number'));
                        // Signature XML
                        $signAttachedDocument = new SignAttachedDocument($company->certificate->path, $company->certificate->password);
                        $signAttachedDocument->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/{$filename}.xml");

                        $at = $signAttachedDocument->sign($attacheddocument)->xml;
                        //      $at = str_replace("&gt;", ">", str_replace("&quot;", '"', str_replace("&lt;", "<", $at)));
                        $file = fopen(StorageService::tempPath("public/{$company->identification_number}/{$filename}".".xml"), "w");
                        fwrite($file, $at);
                        fclose($file);
                        if($this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: documento soporte en adquisiciones efectuadas a no obligados a facturar.' && $this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: Nota de ajuste al documento soporte en adquisiciones efectuadas a sujetos no obligados a expedir factura o documento equivalente')
                            $customer = Customer::findOrFail($this->valueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"));
                        else
                            $customer = Customer::findOrFail($this->valueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/"));
                        $invoice = Document::where('identification_number', '=', $company->identification_number)
                                           ->where('customer', '=', $customer->identification_number)
                                           ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                           ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                           ->where('state_document_id', '=', 0)->get();
                        if(count($invoice) > 0){
                            $invoice[0]->state_document_id = 1;
                            $invoice[0]->cufe = $cufecude;
                            $invoice[0]->save();
                        }
                        if(isset($request))
                            if($request->sendmail){
                                $invoice = Document::where('identification_number', '=', $company->identification_number)
                                                   ->where('customer', '=', $customer->identification_number)
                                                   ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                                   ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                                   ->where('state_document_id', '=', 1)->get();
                                if(count($invoice) > 0 && $customer->identification_number != '222222222222'){
                                    try{
                                        Mail::to($customer->email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, TRUE));
                                        if($request->sendmailtome)
                                            Mail::to($user->email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, FALSE));
                                        if($request->email_cc_list){
                                            foreach($request->email_cc_list as $email)
                                                Mail::to($email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, FALSE));
                                        }
                                        $invoice[0]->send_email_success = 1;
                                        $invoice[0]->save();
                                    } catch (\Exception $m) {
                                        \Log::debug($m->getMessage());
                                    }
                                }
                            }
                    }
                }
                else
                    $at = '';
            } catch (\Exception $e) {
                return $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian));
            }
            $batchFiles = [
                "public/{$company->identification_number}/ReqZIP-{$trackId}.xml",
                "public/{$company->identification_number}/RptaZIP-{$trackId}.xml",
            ];
            if($filename !== '')
                $batchFiles[] = "public/{$company->identification_number}/{$filename}.xml";
            StorageService::uploadBatchIfS3($batchFiles);
            return [
                'message' => 'Consulta generada con éxito',
                'ResponseDian' => $respuestadian,
                'reqzip'=>StorageService::getBase64Auto("public/{$company->identification_number}/ReqZIP-{$trackId}.xml"),
                'rptazip'=>StorageService::getBase64Auto("public/{$company->identification_number}/RptaZIP-{$trackId}.xml"),
                'attacheddocument'=>base64_encode($at),
                'certificate_days_left' => $certificate_days_left,
            ];
        }
    }

    /**
     * Document.
     *
     * @param string $trackId
     *
     * @return array
     */
    public function document(StatusRequest $request, $trackId, $GuardarEn = false)
    {
        // User
        $user = auth()->user();
        // Configura SMTP y remitente (empresa/request -> .env -> usuario SMTP).
        $user->applyMailConfig($request->smtp_parameters);

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

        if($request->is_payroll || $request->is_eqdoc)
            return [
                'success' => false,
                'message' => "Nomina y Documentos Equivalentes no estan disponibles en la version Community."
            ];

        $getStatus = new GetStatus($user->company->certificate->path, $user->company->certificate->password, $user->company->software->url);

        $getStatus->trackId = $trackId;
        $GuardarEn = str_replace("_", "\\", $GuardarEn);

        if ($GuardarEn){
            if (!is_dir($GuardarEn)) {
                mkdir($GuardarEn);
            }
        }
        else{
            StorageService::ensureDirectory("public/{$company->identification_number}");
        }

        $respuestadian = '';
        $typeDocument = TypeDocument::findOrFail(7);
        $resolution = NULL;
        $customer = NULL;
        $cufecude = '';
        $filename = '';
//        $xml = new \DOMDocument;
        $document_generator_warning = "";
        $ar = new \DOMDocument;
        if ($GuardarEn){
            try{
                $respuestadian = $getStatus->signToSend($GuardarEn."\\ReqZIP-".$trackId.".xml")->getResponseToObject($GuardarEn."\\RptaZIP-".$trackId.".xml");
                if(isset($respuestadian->html))
                    return [
                        'success' => false,
                        'message' => "El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde..."
                    ];

                if($respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->IsValid == 'true'){
                    $filename = str_replace('ttr', 'ad', str_replace('pos', 'ad', str_replace('ads', 'ad', str_replace('dse', 'ad', str_replace('ni', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->XmlFileName))))))));
                    if($request->atacheddocument_name_prefix)
                        $filename = $request->atacheddocument_name_prefix.$filename;

                    $cufecude = $respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->XmlDocumentKey;
                    $signedxml = StorageService::getAutoLocal("xml/{$company->id}/".$respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->XmlFileName.".xml");
                    //  $xml->loadXML($signedxml);
                    if(strpos($signedxml, "</Invoice>") > 0)
                        $td = '/Invoice';
                    else
                        if(strpos($signedxml, "</CreditNote>") > 0)
                            $td = '/CreditNote';
                        else
                            if(strpos($signedxml, "</DebitNote>") > 0)
                                $td = '/DebitNote';
                            else
                                if(strpos($signedxml, "</NominaIndividual>") > 0)
                                    $td = '/NominaIndividual';
                                else
                                    if(strpos($signedxml, "</NominaIndividualDeAjuste>") > 0)
                                        $td = '/NominaIndividualDeAjuste';

                    $appresponsexml = base64_decode($respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->XmlBase64Bytes);
                    $ar->loadXML($appresponsexml);
                    $fechavalidacion = $ar->documentElement->getElementsByTagName('IssueDate')->item(0)->nodeValue;
                    $horavalidacion = $ar->documentElement->getElementsByTagName('IssueTime')->item(0)->nodeValue;
                    if($td == '/NominaIndividual' || $td == '/NominaIndividualDeAjuste')
                        $document_number = $this->getTag($signedxml, 'NumeroSecuenciaXML', 0, 'Numero');
                    else
                        $document_number = $this->ValueXML($signedxml, $td."/cbc:ID/");

                    if($td == '/Invoice')
                        if(isset($this->getTag($signedxml, 'Prefix', 0)->nodeValue))
                            $resolution = Resolution::where('prefix', $this->getTag($signedxml, 'Prefix', 0)->nodeValue)->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                        else{
                            if($this->getTag($signedxml, 'InvoiceTypeCode', 0)->nodeValue == 35)
                                $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->firstOrFail();
                            else
                                $resolution = Resolution::where('prefix', NULL)->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                        }
                    //      $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                    else
                        if($td == '/NominaIndividual' || $td == '/NominaIndividualDeAjuste')
                            $resolution = Resolution::where('prefix', $this->getTag($signedxml, 'NumeroSecuenciaXML', 0, 'Prefijo'))->firstOrFail();
                        else
                            $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->firstOrFail();

                    $resolution->document_number = $document_number;
                    // Create XML AttachedDocument
                    $at = '';
                    if($td != '/NominaIndividual' && $td != '/NominaIndividualDeAjuste'){
                        if($this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: documento soporte en adquisiciones efectuadas a no obligados a facturar.' && $this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: Nota de ajuste al documento soporte en adquisiciones efectuadas a sujetos no obligados a expedir factura o documento equivalente')
                            $u = ['name' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name/"),
                                  'email' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'identification_number' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"),
                                  'phone' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'address' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cac:RegistrationAddress/cac:AddressLine/cbc:Line/"),
                                  'type_liability_id' => TypeLiability::where('code', $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:TaxLevelCode/"))->firstOrFail()->id,
                                  'type_regime_id' => "2",
                                  'type_document_identification_id' => TypeDocumentIdentification::where('code', 'Like', '%'.$this->getTag($signedxml, "CompanyID", 2, "schemeName").'%')->firstOrFail()->id,
                                  'dv' => $this->getTag($signedxml, "CompanyID", 2, "schemeID"),
                                 ];
                        else
                            $u = ['name' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name/"),
                                  'email' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'identification_number' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"),
                                  'phone' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                  'address' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cac:RegistrationAddress/cac:AddressLine/cbc:Line/"),
                                  'type_liability_id' => TypeLiability::where('code', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:TaxLevelCode/"))->firstOrFail()->id,
                                  'type_regime_id' => "2",
                                  'type_document_identification_id' => TypeDocumentIdentification::where('code', 'Like', '%'.$this->getTag($signedxml, "CompanyID", 1, "schemeName").'%')->firstOrFail()->id,
                                  'dv' => $this->getTag($signedxml, "CompanyID", 1, "schemeID"),
                                 ];

                        $customer = new user($u);
                        $customer->company = new Company($u);
                        $attacheddocument = $this->createXML(compact('user', 'company', 'customer', 'resolution', 'typeDocument', 'cufecude', 'signedxml', 'appresponsexml', 'fechavalidacion', 'horavalidacion', 'document_number'));
                        // Signature XML
                        $signAttachedDocument = new SignAttachedDocument($company->certificate->path, $company->certificate->password);
                        $signAttachedDocument->GuardarEn = $GuardarEn."\\{$filename}.xml";

                        $at = $signAttachedDocument->sign($attacheddocument)->xml;
                    //      $at = str_replace("&gt;", ">", str_replace("&quot;", '"', str_replace("&lt;", "<", $at)));
                        $file = fopen($GuardarEn."\\{$filename}".".xml", "w");
                    //      $file = fopen($GuardarEn."\\Attachment-".$this->valueXML($signedxml, $td."/cbc:ID/").".xml", "w");
                        fwrite($file, $at);
                        fclose($file);
                        if($this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: documento soporte en adquisiciones efectuadas a no obligados a facturar.' && $this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: Nota de ajuste al documento soporte en adquisiciones efectuadas a sujetos no obligados a expedir factura o documento equivalente')
                            $customer = Customer::findOrFail($this->valueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"));
                        else
                            $customer = Customer::findOrFail($this->valueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/"));

                        $invoice = Document::where('identification_number', '=', $company->identification_number)
                            ->where('customer', '=', $customer->identification_number)
                            ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                            ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                            ->where('state_document_id', '=', 1)->get();
                         if(count($invoice) == 0)
                            $invoice = Document::where('identification_number', '=', $company->identification_number)
                                           ->where('customer', '=', $customer->identification_number)
                                           ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                           ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                           ->where('state_document_id', '=', 0)->get();
                        if(count($invoice) > 0){
                            $invoice[0]->state_document_id = 1;
                            $invoice[0]->cufe = $cufecude;
                            $invoice[0]->save();
                        }
                        if(isset($request))
                            if($request->sendmail){
                                $invoice = Document::where('identification_number', '=', $company->identification_number)
                                                   ->where('customer', '=', $customer->identification_number)
                                                   ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                                   ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                                   ->where('state_document_id', '=', 1)->get();
                                if(count($invoice) > 0 && $customer->company->identification_number != '222222222222'){
                                    try{
                                        Mail::to($customer->email)->send(new InvoiceMail($invoice, $customer, $company, $GuardarEn, FALSE, FALSE, $filename, TRUE, $request));
                                        if($request->sendmailtome)
                                            Mail::to($user->email)->send(new InvoiceMail($invoice, $customer, $company, $GuardarEn, FALSE, FALSE, $filename, FALSE, $request));
                                        if($request->email_cc_list){
                                            foreach($request->email_cc_list as $email)
                                                Mail::to($email)->send(new InvoiceMail($invoice, $customer, $company, $GuardarEn, FALSE, FALSE, $filename, FALSE, $request));
                                        }
                                        $invoice[0]->send_email_success = 1;
                                        $invoice[0]->save();
                                    } catch (\Exception $m) {
                                        \Log::debug($m->getMessage());
                                    }
                                }
                            }
                    }
                }
                else
                  $at = '';
                } catch (\Exception $e) {
                    return $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian));
                }
            return [
                'message' => 'Consulta generada con éxito',
                'ResponseDian' => $respuestadian,
                'reqzip'=>base64_encode(file_get_contents($GuardarEn."\\ReqZIP-{$trackId}.xml")),
                'rptazip'=>base64_encode(file_get_contents($GuardarEn."\\RptaZIP-{$trackId}.xml")),
                'attacheddocument'=>base64_encode($at),
                'cufecude'=>$cufecude,
                'certificate_days_left' => $certificate_days_left,
            ];
        }
        else{
            try{
                $respuestadian = $getStatus->signToSend(StorageService::tempPath("public/{$company->identification_number}/ReqZIP-".$trackId.".xml"))->getResponseToObject(StorageService::tempPath("public/{$company->identification_number}/RptaZIP-".$trackId.".xml"));
                if(isset($respuestadian->html))
                    return [
                        'success' => false,
                        'message' => "El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde..."
                    ];

                if($respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->IsValid == 'true'){
                    $filename = str_replace('ttr', 'ad', str_replace('pos', 'ad', str_replace('ads', 'ad', str_replace('dse', 'ad', str_replace('ni', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->XmlFileName))))))));
                    if($request->atacheddocument_name_prefix)
                        $filename = $request->atacheddocument_name_prefix.$filename;
                    $cufecude = $respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->XmlDocumentKey;
                    $document_generator_warning = "";
                    if(StorageService::existsLocal("xml/{$company->id}/".$respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->XmlFileName.".xml")){
                        $signedxml = StorageService::getAutoLocal("xml/{$company->id}/".$respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->XmlFileName.".xml");
                        $QRStr = $this->getTag($signedxml, 'QRCode', 0)->nodeValue ?? null;
                    }
                    else{
                        $document_generator_warning = ", !!! IMPORTANTE !!!! Este documento no fue generado con la API... ";
                        $xmlDIAN = new XmlDocumentController();
                        $send = [
                            'is_payroll' => false,
                        ];
                        $data_send = json_encode($send);
                        $r = new XmlDocumentRequest($send);
                        $r = $xmlDIAN->document($r, $trackId);
                        if(isset($r['ResponseDian']->Envelope))
                            $signedxml = base64_decode(json_encode($r['ResponseDian']->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->XmlBytesBase64));
                        else{
                            $signedxml = "";
                            $document_generator_warning = $r['ResponseDian'];
                        }
                    }

                    if($signedxml != ''){
                        if(strpos($signedxml, "</Invoice>") > 0)
                            $td = '/Invoice';
                        else
                            if(strpos($signedxml, "</CreditNote>") > 0)
                                $td = '/CreditNote';
                            else
                                if(strpos($signedxml, "</DebitNote>") > 0)
                                    $td = '/DebitNote';
                                else
                                    if(strpos($signedxml, "</NominaIndividual>") > 0)
                                        $td = '/NominaIndividual';
                                    else
                                        if(strpos($signedxml, "</NominaIndividualDeAjuste>") > 0)
                                            $td = '/NominaIndividualDeAjuste';

                    //      $xml->loadXML($signedxml);
                        $appresponsexml = base64_decode($respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->XmlBase64Bytes);
                        $ar->loadXML($appresponsexml);
                        $fechavalidacion = $ar->documentElement->getElementsByTagName('IssueDate')->item(0)->nodeValue;
                        $horavalidacion = $ar->documentElement->getElementsByTagName('IssueTime')->item(0)->nodeValue;
                        if($td == '/NominaIndividual' || $td == '/NominaIndividualDeAjuste')
                            $document_number = $this->getTag($signedxml, 'NumeroSecuenciaXML', 0, 'Numero');
                        else
                            $document_number = $this->ValueXML($signedxml, $td."/cbc:ID/");

                    //      if($td == '/Invoice')
                    //          if(isset($this->getTag($signedxml, 'Prefix', 0)->nodeValue))
                    //              $resolution = Resolution::where('prefix', $this->getTag($signedxml, 'Prefix', 0)->nodeValue)->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                    //          else{
                    //              if($this->getTag($signedxml, 'InvoiceTypeCode', 0)->nodeValue == 35)
                    //                  $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->firstOrFail();
                    //              else
                    //                  $resolution = Resolution::where('prefix', NULL)->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                    //          }
                    //          $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->where('resolution', $this->getTag($signedxml, 'InvoiceAuthorization', 0)->nodeValue)->firstOrFail();
                    //      else
                    //          if($td == '/NominaIndividual' || $td == '/NominaIndividualDeAjuste')
                    //              $resolution = Resolution::where('prefix', $this->getTag($signedxml, 'NumeroSecuenciaXML', 0, 'Prefijo'))->firstOrFail();
                    //          else
                    //              $resolution = Resolution::where('prefix', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))->firstOrFail();

                        $resolution = new Resolution();
                        $resolution->document_number = $document_number;
                        // Create XML AttachedDocument
                        $at = '';
                        if($td != '/NominaIndividual' && $td != '/NominaIndividualDeAjuste'){
                            if($this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: documento soporte en adquisiciones efectuadas a no obligados a facturar.' && $this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: Nota de ajuste al documento soporte en adquisiciones efectuadas a sujetos no obligados a expedir factura o documento equivalente')
                                $u = ['name' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name/"),
                                      'email' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                      'identification_number' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"),
                                      'phone' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                      'address' => $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cac:RegistrationAddress/cac:AddressLine/cbc:Line/"),
                                      'type_liability_id' => TypeLiability::where('code', $this->ValueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:TaxLevelCode/"))->firstOrFail()->id,
                                      'type_regime_id' => "2",
                                      'type_document_identification_id' => TypeDocumentIdentification::where('code', 'Like', '%'.$this->getTag($signedxml, "CompanyID", 2, "schemeName").'%')->firstOrFail()->id,
                                      'dv' => $this->getTag($signedxml, "CompanyID", 2, "schemeID"),
                                     ];
                            else
                                $u = ['name' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name/"),
                                      'email' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                      'identification_number' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"),
                                      'phone' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail/"),
                                      'address' => $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cac:RegistrationAddress/cac:AddressLine/cbc:Line/"),
                                      'type_liability_id' => TypeLiability::where('code', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:TaxLevelCode/"))->firstOrFail()->id,
                                      'type_regime_id' => "2",
                                      'type_document_identification_id' => TypeDocumentIdentification::where('code', 'Like', '%'.$this->getTag($signedxml, "CompanyID", 1, "schemeName").'%')->firstOrFail()->id,
                                      'dv' => $this->getTag($signedxml, "CompanyID", 1, "schemeID"),
                                     ];

                            $customer = new user($u);
                            $customer->company = new Company($u);
                            $attacheddocument = $this->createXML(compact('user', 'company', 'customer', 'resolution', 'typeDocument', 'cufecude', 'signedxml', 'appresponsexml', 'fechavalidacion', 'horavalidacion', 'document_number'));
                    //          return $attacheddocument->saveXML();
                            // Signature XML
                            $signAttachedDocument = new SignAttachedDocument($company->certificate->path, $company->certificate->password);
                            $signAttachedDocument->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/{$filename}.xml");

                            $at = $signAttachedDocument->sign($attacheddocument)->xml;
                    //          $at = str_replace("&gt;", ">", str_replace("&quot;", '"', str_replace("&lt;", "<", $at)));
                            $file = fopen(StorageService::tempPath("public/{$company->identification_number}/{$filename}".".xml"), "w");
                    //          $file = fopen(StorageService::tempPath("public/{$company->identification_number}/Attachment-".$this->valueXML($signedxml, $td."/cbc:ID/").".xml"), "w");
                            fwrite($file, $at);
                            fclose($file);
                            if($this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: documento soporte en adquisiciones efectuadas a no obligados a facturar.' && $this->valueXML($signedxml, $td."/cbc:ProfileID/") != 'DIAN 2.1: Nota de ajuste al documento soporte en adquisiciones efectuadas a sujetos no obligados a expedir factura o documento equivalente')
                                $customer = Customer::findOrFail($this->valueXML($signedxml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID/"));
                            else
                                $customer = Customer::findOrFail($this->valueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/"));
                            $invoice = Document::where('identification_number', '=', $company->identification_number)
                                ->where('customer', '=', $customer->identification_number)
                                ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                ->where('state_document_id', '=', 1)->get();
                             if(count($invoice) == 0)
                                $invoice = Document::where('identification_number', '=', $company->identification_number)
                                               ->where('customer', '=', $customer->identification_number)
                                               ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                               ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                               ->where('state_document_id', '=', 0)->get();
                            if(count($invoice) > 0){
                                $invoice[0]->state_document_id = 1;
                                $invoice[0]->cufe = $cufecude;
                                $invoice[0]->save();
                            }
                            if(isset($request))
                                if($request->sendmail){
                                    $invoice = Document::where('identification_number', '=', $company->identification_number)
                                                       ->where('customer', '=', $customer->identification_number)
                                                       ->where('prefix', '=', $this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"))
                                                       ->where('number', '=', str_replace($this->ValueXML($signedxml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:CorporateRegistrationScheme/cbc:ID/"), '', $this->ValueXML($signedxml, $td."/cbc:ID/")))
                                                       ->where('state_document_id', '=', 1)->get();
                                    if((count($invoice) > 0 && $customer->identification_number != '222222222222')){
                                        try{
                                            Mail::to($customer->email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, TRUE, $request));
                                            if($request->sendmailtome)
                                                Mail::to($user->email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
                                            if($request->email_cc_list){
                                                foreach($request->email_cc_list as $email)
                                                    Mail::to($email)->send(new InvoiceMail($invoice, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
                                            }
                                            $invoice[0]->send_email_success = 1;
                                            $invoice[0]->save();
                                        } catch (\Exception $m) {
                                            \Log::debug($m->getMessage());
                                        }
                                    }
                                }
                        }
                    }
                }
                else
                    $at = '';
            } catch (\Exception $e) {
                return $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian));
            }
            $batchFiles = [
                "public/{$company->identification_number}/ReqZIP-{$trackId}.xml",
                "public/{$company->identification_number}/RptaZIP-{$trackId}.xml",
            ];
            if($filename !== '')
                $batchFiles[] = "public/{$company->identification_number}/{$filename}.xml";
            StorageService::uploadBatchIfS3($batchFiles);
            return [
                'message' => 'Consulta generada con éxito'.$document_generator_warning,
                'ResponseDian' => $respuestadian,
                'reqzip'=>StorageService::getBase64Auto("public/{$company->identification_number}/ReqZIP-{$trackId}.xml"),
                'rptazip'=>StorageService::getBase64Auto("public/{$company->identification_number}/RptaZIP-{$trackId}.xml"),
                'attacheddocument'=>base64_encode($at),
                'cufecude'=>$cufecude,
                'certificate_days_left' => $certificate_days_left,
            ];
        }
    }

    /**
     * Events Document.
     *
     * @param string $trackId
     *
     * @return array
     */
    public function events_document($trackId)
    {
        // User
        $user = auth()->user();

        $company = $user->company;

        // Verify Certificate
        $certificate_days_left = 0;
        $c = $this->verify_certificate();
        if(!$c['success'])
            return $c;
        else
            $certificate_days_left = $c['certificate_days_left'];

        $getStatus = new GetStatusEvents($user->company->certificate->path, $user->company->certificate->password, $user->company->software->url);

        $getStatus->trackId = $trackId;
        StorageService::ensureDirectory("public/{$company->identification_number}");

        $respuestadian = '';
        $typeDocument = TypeDocument::findOrFail(7);
        $resolution = NULL;
        $customer = NULL;
        $cufecude = '';
//        $xml = new \DOMDocument;
        $ar = new \DOMDocument;
        try{
            $respuestadian = $getStatus->signToSend(StorageService::tempPath("public/{$company->identification_number}/ReqEVENTS-".$trackId.".xml"))->getResponseToObject(StorageService::tempPath("public/{$company->identification_number}/RptaEVENTS-".$trackId.".xml"));
            if(isset($respuestadian->html))
                return [
                    'success' => false,
                    'message' => "El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde..."
                ];

            $cufecude = $respuestadian->Envelope->Body->GetStatusEventResponse->GetStatusEventResult->XmlDocumentKey;
            if($respuestadian->Envelope->Body->GetStatusEventResponse->GetStatusEventResult->IsValid == 'true'){
                $appresponsexml = base64_decode($respuestadian->Envelope->Body->GetStatusEventResponse->GetStatusEventResult->XmlBase64Bytes);
                $i = 0;
                $events = [];
                while($this->getQuery($appresponsexml, 'cac:DocumentResponse', true, $i) != null){
                    $event_number = $this->getQuery($appresponsexml, 'cac:DocumentResponse/cac:Response/cbc:ReferenceID', true, $i)->nodeValue;
                    $event_code = $this->getQuery($appresponsexml, 'cac:DocumentResponse/cac:Response/cbc:ResponseCode', true, $i)->nodeValue;
                    $event_description = $this->getQuery($appresponsexml, 'cac:DocumentResponse/cac:Response/cbc:Description', true, $i)->nodeValue;
                    $event_date = $this->getQuery($appresponsexml, 'cac:DocumentResponse/cac:Response/cbc:EffectiveDate', true, $i)->nodeValue;
                    $event_time = $this->getQuery($appresponsexml, 'cac:DocumentResponse/cac:Response/cbc:EffectiveTime', true, $i)->nodeValue;
                    $event_cude = $this->getQuery($appresponsexml, 'cac:DocumentResponse/cac:DocumentReference/cbc:UUID', true, $i)->nodeValue;

                    array_push($events, [
                        'event_number' => $event_number,
                        'dian_code' => $event_code,
                        'description' => $event_description,
                        'date' => $event_date,
                        'time' => $event_time,
                        'cude' => $event_cude
                    ]);
                    $i++;
                }
//                $ar->loadXML($appresponsexml);
            }
            else
                return [
                    'success' => false,
                    'message' => 'Consulta de eventos generada con exito ',
                    'ResponseDian' => $respuestadian,
                    'cufecude'=>$cufecude,
                    'certificate_days_left' => $certificate_days_left,
                ];
            } catch (\Exception $e) {
                return $this->is_error($e->getMessage());
        }
        return [
            'success' => true,
            'message' => 'Consulta de eventos generada con exito ',
            'events' => $events,
            'ResponseDian' => $respuestadian,
            'cufecude'=>$cufecude,
            'certificate_days_left' => $certificate_days_left,
        ];
    }
}
