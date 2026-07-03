<?php

namespace App\Http\Controllers\Api;

use App\ReceivedDocument;
use Illuminate\Http\Request;
use App\Traits\DocumentTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\XmlDocumentRequest;
use Carbon\Carbon;

class RadianEventController extends Controller
{
    use DocumentTrait;

    private function getResponse($success, $message)
    {
        return [
            'success' => $success,
            'message' => $message,
        ];
    }

    protected function processSellerDocumentReception(Request $request)
    {

        try
        {
            $att = new \DOMDocument('1.0', 'utf-8');
            $att->preserveWhiteSpace = false;
            $att->formatOutput = true;

            $company_idnumber = $request->company_idnumber;
            $attXMLStr = str_replace("&", "&amp;", $request->xml_document);

            if(!$att->loadXML(base64_decode($attXMLStr))){
                return $this->getResponse(false, "El archivo no se pudo cargar, revise los problemas asociados");
            }

            else{

                if(!strpos($att->saveXML(), "<AttachedDocument")){
                    return $this->getResponse(false, "El archivo  no es un AttachedDocument XML");
                }

                if(!strpos($att->saveXML(), "<ApplicationResponse")){
                    return $this->getResponse(false, "El archivo no se encontro el ApplicationResponse dentro del AttachedDocument XML");
                }

                if(!strpos($att->saveXML(), "<Invoice")){
                    return $this->getResponse(false, "el archivo no corresponde al AttachedDocument XML de un documento Invoice");
                }

                $invoiceXMLStr = $att->documentElement->getElementsByTagName('Description')->item(0)->nodeValue;
                $invoiceXMLStr = str_replace("&", "&amp;", substr(base64_decode($attXMLStr), strpos(base64_decode($attXMLStr), "<Invoice"), strpos(base64_decode($attXMLStr), "/Invoice>") - strpos(base64_decode($attXMLStr), "<Invoice") + 9));
                $invoiceXMLStr = preg_replace("/[\r\n|\n|\r]+/", "","<?xml version=\"1.0\" encoding=\"utf-8\"?>".$invoiceXMLStr);

                $invoice_doc = new \stdClass;
                $invoice_doc->identification_number = $this->ValueXML($invoiceXMLStr, "/Invoice/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                $invoice_doc->dv = $this->validarDigVerifDIAN($invoice_doc->identification_number);
                $invoice_doc->name_seller = $this->getTag($invoiceXMLStr, 'RegistrationName', 0)->nodeValue;
                $invoice_doc->state_document_id = 1;
                $invoice_doc->type_document_id = $this->getTag($invoiceXMLStr, 'InvoiceTypeCode', 0)->nodeValue;
                $invoice_doc->customer = $this->ValueXML($invoiceXMLStr, "/Invoice/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                if(strpos($invoiceXMLStr, "</sts:Prefix>"))
                    $invoice_doc->prefix = $this->getTag($invoiceXMLStr, 'Prefix', 0)->nodeValue;
                else
                    $invoice_doc->prefix = "";
                $i = 0;
                if($invoice_doc->prefix != "")
                    do{
//                            $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
                        $invoice_doc->number =  $this->getTag($invoiceXMLStr, "ID", $i)->nodeValue;
                        $i++;
                    }while(strpos($invoice_doc->number, $invoice_doc->prefix) === false);
                else
                    $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");

                $invoice_doc->xml = null;
                $invoice_doc->cufe = $this->getTag($invoiceXMLStr, 'UUID', 0)->nodeValue;
                $invoice_doc->date_issue = $this->getTag($invoiceXMLStr, 'IssueDate', 0)->nodeValue.' '.str_replace('-05:00', '', $this->getTag($invoiceXMLStr, 'IssueTime', 0)->nodeValue);
                $invoice_doc->sale = $this->getTag($invoiceXMLStr, 'TaxInclusiveAmount', 0)->nodeValue;
                if(isset($this->getTag($invoiceXMLStr, 'AllowanceTotalAmount', 0)->nodeValue))
                    $invoice_doc->total_discount =  $this->getTag($invoiceXMLStr, 'AllowanceTotalAmount', 0)->nodeValue;
                else
                    $invoice_doc->total_discount = 0;
                $invoice_doc->subtotal = $this->getTag($invoiceXMLStr, 'LineExtensionAmount', 0)->nodeValue;
                $invoice_doc->total_tax = $invoice_doc->sale - $invoice_doc->subtotal;
                $invoice_doc->total = $this->getTag($invoiceXMLStr, 'PayableAmount', 0)->nodeValue;
                $invoice_doc->ambient_id = $this->getTag($invoiceXMLStr, 'ProfileExecutionID', 0)->nodeValue;
                $invoice_doc->pdf = null;
                $invoice_doc->acu_recibo = 0;
                $invoice_doc->rec_bienes = 0;
                $invoice_doc->aceptacion = 0;
                $invoice_doc->rechazo = 0;

                if($invoice_doc->customer != $company_idnumber){
                    return $this->getResponse(false, "El archivo  no corresponde un AttachedDocument XML del adquiriente ".$company_idnumber);

                }

                $exists = ReceivedDocument::where('customer', $company_idnumber)->where('identification_number', $invoice_doc->identification_number)->where('prefix', $invoice_doc->prefix)->where('number', $invoice_doc->number)->get();

                if(count($exists) == 0)
                {
                    return [
                        'success' => true,
                        'message' => "El archivo fue cargado satisfactoriamente...",
                        'data' => $invoice_doc,
                    ];
                }
                else{
                    return $this->getResponse(false, "El archivo  ya existe en la base de datos...");
                }

                return $this->getResponse(true, "El archivo fue cargado satisfactoriamente...");

            }

        }
        catch (\Exception $e)
        {
            return $this->getResponse(false, "Error en la carga: {$e->getMessage()}");
        }
    }

    /**
     * Cargar datos de un documento desde la DIAN usando el CUFE, sin enviar evento.
     * Opcionalmente guarda el registro en received_documents.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function loadDocumentByCufe(Request $request)
    {
        try {
            // User company
            $user = auth()->user();
            $company = $user->company;

            // Validar CUFE requerido
            if (!$request->cufe) {
                return $this->getResponse(false, 'El campo cufe es requerido.');
            }

            // Verificar que la empresa esté activa
            if ($company->state == false) {
                return $this->getResponse(false, 'La empresa se encuentra en el momento INACTIVA para enviar documentos electronicos...');
            }

            // Verificar que el software esté configurado
            if (!$company->software) {
                return $this->getResponse(false, 'La empresa no tiene configurado el software de facturación.');
            }

            // Consultar XML en la DIAN por CUFE (verificarEstadoDIAN y verify_certificate se ejecutan dentro de XmlDocumentController)
            $xmlDIAN = new XmlDocumentController();
            $send = ['is_event' => true];
            $r = $xmlDIAN->document(new XmlDocumentRequest($send), $request->cufe);

            if (!$r['success']) {
                return [
                    'success' => false,
                    'message' => $r['ResponseDian'] ?? 'No se pudo obtener el documento de la DIAN.',
                ];
            }

            $invoiceXMLStr = base64_decode(json_encode($r['ResponseDian']->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->XmlBytesBase64));

            if (strpos($invoiceXMLStr, "<Invoice") === false) {
                return $this->getResponse(false, 'El CUFE ingresado no corresponde al XML de un documento Invoice.');
            }

            // Extraer datos del documento
            $invoice_doc = new \stdClass;
            $invoice_doc->identification_number = $this->getQuery($invoiceXMLStr, "cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID")->nodeValue;
            $invoice_doc->dv = $this->validarDigVerifDIAN($invoice_doc->identification_number);
            $invoice_doc->name_seller = $this->getTag($invoiceXMLStr, 'RegistrationName', 0)->nodeValue;
            $invoice_doc->state_document_id = 1;
            $invoice_doc->type_document_id = $this->getTag($invoiceXMLStr, 'InvoiceTypeCode', 0)->nodeValue;
            $invoice_doc->customer = $this->getQuery($invoiceXMLStr, "cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID")->nodeValue;

            // Intentar obtener nombre del cliente
            try {
                $invoice_doc->customer_name = $this->getQuery($invoiceXMLStr, "cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:RegistrationName")->nodeValue;
            } catch (\Exception $e) {
                try {
                    $invoice_doc->customer_name = $this->getQuery($invoiceXMLStr, "cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name")->nodeValue;
                } catch (\Exception $e) {
                    $invoice_doc->customer_name = '';
                }
            }

            // Prefix
            if (strpos($invoiceXMLStr, "</sts:Prefix>")) {
                $invoice_doc->prefix = $this->getQuery($invoiceXMLStr, 'ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sts:DianExtensions/sts:InvoiceControl/sts:AuthorizedInvoices/sts:Prefix', 0)->nodeValue;
            } else {
                $invoice_doc->prefix = "";
            }

            // Number
            $i = 0;
            if ($invoice_doc->prefix != "") {
                do {
                    $invoice_doc->number = $this->getTag($invoiceXMLStr, "ID", $i)->nodeValue;
                    $i++;
                } while (strpos($invoice_doc->number, $invoice_doc->prefix) === false);
            } else {
                $invoice_doc->number = $this->getQuery($invoiceXMLStr, "cbc:ID")->nodeValue;
            }

            $invoice_doc->cufe = $this->getTag($invoiceXMLStr, 'UUID', 0)->nodeValue;
            $invoice_doc->date_issue = $this->getTag($invoiceXMLStr, 'IssueDate', 0)->nodeValue . ' ' . str_replace('-05:00', '', $this->getTag($invoiceXMLStr, 'IssueTime', 0)->nodeValue);
            $invoice_doc->sale = $this->getTag($invoiceXMLStr, 'TaxInclusiveAmount', 0)->nodeValue;

            if (isset($this->getTag($invoiceXMLStr, 'AllowanceTotalAmount', 0)->nodeValue)) {
                $invoice_doc->total_discount = $this->getTag($invoiceXMLStr, 'AllowanceTotalAmount', 0)->nodeValue;
            } else {
                $invoice_doc->total_discount = 0;
            }

            $invoice_doc->subtotal = $this->getTag($invoiceXMLStr, 'LineExtensionAmount', 0)->nodeValue;
            $invoice_doc->total_tax = $invoice_doc->sale - $invoice_doc->subtotal;
            $invoice_doc->total = $this->getTag($invoiceXMLStr, 'PayableAmount', 0)->nodeValue;
            $invoice_doc->ambient_id = $this->getTag($invoiceXMLStr, 'ProfileExecutionID', 0)->nodeValue;
            $invoice_doc->pdf = null;
            $invoice_doc->acu_recibo = 0;
            $invoice_doc->rec_bienes = 0;
            $invoice_doc->aceptacion = 0;
            $invoice_doc->rechazo = 0;

            // Verificar si el adquiriente corresponde a la empresa
            if ($invoice_doc->customer != $company->identification_number) {
                return [
                    'success' => false,
                    'message' => "El adquiriente del documento no corresponde a la empresa con nit: " . $company->identification_number,
                    'data' => $invoice_doc,
                ];
            }

            // Verificar si ya existe en received_documents
            $exists = ReceivedDocument::where('customer', $company->identification_number)
                ->where('identification_number', $invoice_doc->identification_number)
                ->where('prefix', $invoice_doc->prefix)
                ->where('number', $invoice_doc->number)
                ->get();

            if (count($exists) > 0) {
                return [
                    'success' => true,
                    'message' => 'El documento ya existe en la base de datos.',
                    'data' => $exists[0],
                    'already_exists' => true,
                ];
            }

            // Guardar si se solicita
            if ($request->save && $request->save == true) {
                $receivedDoc = new ReceivedDocument();
                $receivedDoc->identification_number = $invoice_doc->identification_number;
                $receivedDoc->dv = $invoice_doc->dv;
                $receivedDoc->name_seller = $invoice_doc->name_seller;
                $receivedDoc->state_document_id = $invoice_doc->state_document_id;
                $receivedDoc->type_document_id = $invoice_doc->type_document_id;
                $receivedDoc->customer = $invoice_doc->customer;
                $receivedDoc->customer_name = $invoice_doc->customer_name ?? '';
                $receivedDoc->prefix = $invoice_doc->prefix;
                $receivedDoc->number = $invoice_doc->number;
                $receivedDoc->xml = "no-attached-document";
                $receivedDoc->cufe = $invoice_doc->cufe;
                $receivedDoc->date_issue = $invoice_doc->date_issue;
                $receivedDoc->sale = $invoice_doc->sale;
                $receivedDoc->total_discount = $invoice_doc->total_discount;
                $receivedDoc->subtotal = $invoice_doc->subtotal;
                $receivedDoc->total_tax = $invoice_doc->total_tax;
                $receivedDoc->total = $invoice_doc->total;
                $receivedDoc->ambient_id = $invoice_doc->ambient_id;
                $receivedDoc->pdf = "no-attached-document";
                $receivedDoc->acu_recibo = 0;
                $receivedDoc->rec_bienes = 0;
                $receivedDoc->aceptacion = 0;
                $receivedDoc->rechazo = 0;
                $receivedDoc->save();

                return [
                    'success' => true,
                    'message' => 'El documento fue cargado y guardado satisfactoriamente.',
                    'data' => $receivedDoc,
                    'saved' => true,
                ];
            }

            return [
                'success' => true,
                'message' => 'Datos del documento obtenidos satisfactoriamente.',
                'data' => $invoice_doc,
                'saved' => false,
            ];

        } catch (\Exception $e) {
            return $this->getResponse(false, "Error al cargar documento por CUFE: {$e->getMessage()}");
        }
    }


}
