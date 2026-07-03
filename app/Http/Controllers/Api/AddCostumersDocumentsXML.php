<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\DocumentTrait;
use ubl21dian\Templates\SOAP\GetStatus;
use App\Customer;
use App\Document;
use App\Company;
use App\User;
use App\Resolution;
use App\TypeDocument;
use App\Services\StorageService;

class AddCostumersDocumentsXML extends Controller
{
    use DocumentTrait;

    private function environment($id_env, $company, $id_software){
        if($id_software)
            $company->software->update([
                'identifier' => $id_software,
            ]);

        $company->update([
            'type_environment_id' => $id_env,
        ]);

        if ($id_env == 1)
          $company->software->update([
              'url' => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc',
          ]);
        else
          $company->software->update([
              'url' => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
          ]);
    }

    /**
     * Organize.
     *
     *
     */

    public function Organize($nit){
        $company = Company::where('identification_number', $nit)->first();
        $user = User::find($company->user_id);
        $typeDocument = TypeDocument::findOrFail(7);
        $resolution = NULL;
        $customer = NULL;
        $respuestadian = '';
        $ar = new \DOMDocument;
        $xmls = [];
        $files = array_diff(scandir(StorageService::tempPath("public/{$nit}")), array('..', '.'));
        foreach($files as $f){
            if((substr($f, 0, 4) == 'FES-' || substr($f, 0, 4) == 'NCS-' || substr($f, 0, 4) == 'NDS-') && (strtoupper(substr($f, strlen($f) - 3, 3)) == 'XML'))
                array_push($xmls, $f);
        }
        foreach($xmls as $x){
            $xml = StorageService::getAutoLocal("public/{$nit}/{$x}");
            if(strpos($xml, "</CreditNote>") > 0)
                $td = '/CreditNote';
            else
                if(strpos($xml, "</DebitNote>") > 0)
                    $td = '/DebitNote';
                else
                    if(strpos($xml, "</Invoice>") > 0)
                        $td = '/Invoice';
            $customer = Customer::where('identification_number', '=', $this->ValueXML($xml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/"))->first();
            if(is_null($customer)){
                $customer = new Customer();
                $customer->identification_number = $this->ValueXML($xml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                $customer->name = $this->ValueXML($xml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name/");
                $customer->phone = $this->ValueXML($xml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:Telephone/");
                if(strpos($this->ValueXML($xml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail/"), '<cbc:Line>') >= 0)
                    $customer->address = $this->ValueXML($xml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cac:RegistrationAddress/cac:AddressLine/cbc:Line/");
                else
                    $customer->address = $this->ValueXML($xml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cac:RegistrationAddress/cac:AddressLine/");
                $customer->email = $this->ValueXML($xml, $td."/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail/");
                $this->registerCustomer($customer, true, true);
            }
            $document = Document::where('cufe', '=', $this->ValueXML($xml, $td."/cbc:UUID/"))->where('state_document_id', '=', 1)->first();
            if(is_null($document)){
                $document = new Document();
                $document->identification_number = $this->ValueXML($xml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                $document->identification_number = $this->ValueXML($xml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                $document->state_document_id = 1;
                if($td == '/Invoice')
                    $document->type_document_id = 1;
                else
                    if($td == '/CreditNote')
                        $document->type_document_id = 4;
                    else
                        $document->type_document_id = 5;
                $document->customer = $this->ValueXML($xml, $td."/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                if($td == '/Invoice'){
                    $document->prefix = $this->ValueXML($xml, $td."/ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sts:DianExtensions/sts:InvoiceControl/sts:AuthorizedInvoices/sts:Prefix/");
                    $document->xml = 'FES-';
                }
                else{
                    $company = Company::where('identification_number','=', $nit)->first();
                    if($td == '/CreditNote'){
                        $resolutions = Resolution::where('company_id', '=', $company->id)->where('type_document_id', '=', 4)->first();
                        $document->xml = 'NCS-';
                    }
                    else{
                        $resolutions = $Resolution::where('company_id', '=', $company->id)->where('type_document_id', '=', 5)->first();
                        $document->xml = 'NDS-';
                    }
                    $document->prefix = $resolutions->prefix;
                }
                $document->number = substr($this->ValueXML($xml, $td."/cbc:ID/"), strlen($document->prefix));
                $filename_xml_pdf = $document->xml.$document->prefix.$document->number;
                $document->xml = $filename_xml_pdf.'.xml';
                $document->cufe = $this->ValueXML($xml, $td."/cbc:UUID/");
                $document->client_id = $this->ValueXML($xml, $td."/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                $document->client = json_encode($this->ValueXML($xml, $td."/cac:AccountingSupplierParty/"));
                $document->currency_id = 35;
                $document->date_issue = $this->ValueXML($xml, $td."/cbc:IssueDate/").' '.substr($this->ValueXML($xml, $td."/cbc:IssueTime/"), 0, strpos($this->ValueXML($xml, $td."/cbc:IssueTime/"), '-05:00'));
                $document->sale = $this->ValueXML($xml, $td."/cac:LegalMonetaryTotal/cbc:PayableAmount/");
                if($td == '/Invoice'){
                    $document->total_discount = $this->ValueXML($xml, $td."/cac:LegalMonetaryTotal/cbc:AllowanceTotalAmount/");
                    $document->taxes =  $this->ValueXML($xml, $td."/cac:TaxTotal/");
                    $document->total_tax = $this->ValueXML($xml, $td."/cbc:TaxInclusiveAmount/") - $this->ValueXML($xml, $td."/cbc:TaxExclusiveAmount/");
                }
                else{
                    $document->total_discount = 0;
                    $document->taxes =  "";
                    $document->total_tax = 0;
                }
                $document->subtotal = $this->ValueXML($xml, $td."/cac:LegalMonetaryTotal/cbc:LineExtensionAmount/");
                $document->total = $this->ValueXML($xml, $td."/cac:LegalMonetaryTotal/cbc:PayableAmount/");
                $document->version_ubl_id = 2;
                $document->ambient_id = $this->ValueXML($xml, $td."/cbc:ProfileExecutionID/");
                $document->pdf = $filename_xml_pdf.'.pdf';
                $document->save();
            }
            $id_env_actual = $company->type_environment_id;
            $id_software_actual = $company->software->identifier;
            $attachmentRelativePath = "public/{$company->identification_number}/Attachment-".$this->valueXML($xml, $td."/cbc:ID/").".xml";
            if(!StorageService::exists($attachmentRelativePath)){
                $this->environment($document->ambient_id, $company, $this->ValueXML($xml, $td."/ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sts:DianExtensions/sts:SoftwareProvider/sts:SoftwareID/"));
                $getStatus = new GetStatus($company->certificate->path, $company->certificate->password, $company->software->url);
                $getStatus->trackId = $document->cufe;
                $respuestadian = $getStatus->signToSend(StorageService::tempPath("public/{$nit}/ReqZIP-".$document->cufe.".xml"))->getResponseToObject(StorageService::tempPath("public/{$nit}/RptaZIP-".$document->cufe.".xml"));
                $this->environment($id_env_actual, $company, $id_software_actual);
                if($respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->IsValid == 'true'){
                    $appresponsexml = base64_decode($respuestadian->Envelope->Body->GetStatusResponse->GetStatusResult->XmlBase64Bytes);
                    $ar->loadXML($appresponsexml);
                    $fechavalidacion = $ar->documentElement->getElementsByTagName('IssueDate')->item(0)->nodeValue;
                    $horavalidacion = $ar->documentElement->getElementsByTagName('IssueTime')->item(0)->nodeValue;
                    // Create XML AttachedDocument
                    $cufecude = $document->cufe;
                    $customer = NULL;
                    $signedxml = $xml;
                    $attacheddocument = $this->createXML(compact('user', 'company', 'customer', 'resolution', 'typeDocument', 'cufecude', 'signedxml', 'appresponsexml', 'fechavalidacion', 'horavalidacion'));
                    $at = $attacheddocument->saveXML();
//                    $at = str_replace("&gt;", ">", str_replace("&quot;", '"', str_replace("&lt;", "<", $at)));
                    $localAttachmentPath = StorageService::tempPath($attachmentRelativePath);
                    StorageService::ensureDirectory("public/{$company->identification_number}");
                    $file = fopen($localAttachmentPath, "w");
                    fwrite($file, $at);
                    fclose($file);
                    StorageService::putLocalFile($attachmentRelativePath, $localAttachmentPath, false);
                }
                else
                     $at = '';
            }
        }
    }
}
