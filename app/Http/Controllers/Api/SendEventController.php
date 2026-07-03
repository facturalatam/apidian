<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Company;
use App\TypeDocument;
use App\TypeRejection;
use App\DocumentReference;
use App\IssuerParty;
use App\Event;
use App\Customer;
use App\Tax;
use App\TypeOrganization;
use App\TypeRegime;
use App\Document;
use App\ReceivedDocument;
use Illuminate\Http\Request;
use App\Traits\DocumentTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendEventRequest;
use App\Http\Requests\Api\SendEventDataRequest;
use App\Http\Requests\Api\XmlDocumentRequest;
use ubl21dian\XAdES\SignEvent;
use ubl21dian\Templates\SOAP\SendEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventMail;
use Goutte\Client;
use Carbon\Carbon;
use DateTime;
use Storage;
use App\Services\StorageService;

class SendEventController extends Controller
{
    use DocumentTrait;

    public function queryeventscufedian($cufe, $ambiente){
        $client = new Client();
        if ($ambiente == 1 || $ambiente == "1")
            $crawler = $client->request('GET', "https://catalogo-vpfe.dian.gov.co/document/ShowDocumentToPublic/{$cufe}");
        else
            $crawler = $client->request('GET', "https://catalogo-vpfe-hab.dian.gov.co/document/ShowDocumentToPublic/{$cufe}");

        $events_list = [
            "Evento_030" => [],
            "Evento_031" => [],
            "Evento_032" => [],
            "Evento_033" => [],
            "Evento_034" => [],
        ];
        $events = $crawler->filter('#container1 > .table-responsive > table >tbody>tr')->text("Sin eventos!");
        if ($events !== "Sin eventos!") {
            $nodeValues = $crawler->filter('td')->each(function ($node, $i) {
                return $node->text();
            });
            // Se obtiene la posición del evento en la data obtenida y se convierte a valor, ojo no a número
            $event_1 = strval(array_search("030", $nodeValues));
            $event_2 = strval(array_search("031", $nodeValues));
            $event_3 = strval(array_search("032", $nodeValues));
            $event_4 = strval(array_search("033", $nodeValues));
            $event_5 = strval(array_search("034", $nodeValues));
            // return "-" . $event_1 . "-". $event_2 . "-" . $event_3 . "-". $event_4 . "-" . "$event_5";
            if (!empty($event_1) || $event_1 == 0) {
                array_push(
                    $events_list['Evento_030'],
                    [
                        'success' => "true",
                        'Evento_030' => $nodeValues[$event_1],
                        'Descripción' => $nodeValues[$event_1 + 1],
                        'Fecha' =>  $nodeValues[$event_1 + 2],
                        'Nit_Emisor' =>  $nodeValues[$event_1 + 3],
                        'Emisor' =>  $nodeValues[$event_1 + 4],
                        'Nit_Receptor' =>  $nodeValues[$event_1 + 5],
                        'Receptor' =>  $nodeValues[$event_1 + 6],
                    ]
                );
            }
            if (!empty($event_2) || $event_2 == 9) {
                array_push(
                    $events_list['Evento_031'],
                    [
                        'success' => "true",
                        'Evento_031' => $nodeValues[$event_2],
                        'Descripción' => $nodeValues[$event_2 + 1],
                        'Fecha' =>  $nodeValues[$event_2 + 2],
                        'Nit_Emisor' =>  $nodeValues[$event_2 + 3],
                        'Emisor' =>  $nodeValues[$event_2 + 4],
                        'Nit_Receptor' =>  $nodeValues[$event_2 + 5],
                        'Receptor' =>  $nodeValues[$event_2 + 6],
                    ]
                );
            }
            if (!empty($event_3) || $event_3 == 17) {
                array_push(
                    $events_list['Evento_032'],
                    [
                        'success' => "true",
                        'Evento_032' => $nodeValues[$event_3],
                        'Descripción' => $nodeValues[$event_3 + 1],
                        'Fecha' =>  $nodeValues[$event_3 + 2],
                        'Nit_Emisor' =>  $nodeValues[$event_3 + 3],
                        'Emisor' =>  $nodeValues[$event_3 + 4],
                        'Nit_Receptor' =>  $nodeValues[$event_3 + 5],
                        'Receptor' =>  $nodeValues[$event_3 + 6],
                    ]
                );
            }
            if (!empty($event_4) || $event_4 == 26) {
                array_push(
                    $events_list['Evento_033'],
                    [
                        'success' => "true",
                        'Evento_033' => $nodeValues[$event_4],
                        'Descripción' => $nodeValues[$event_4 + 1],
                        'Fecha' =>  $nodeValues[$event_4 + 2],
                        'Nit_Emisor' =>  $nodeValues[$event_4 + 3],
                        'Emisor' =>  $nodeValues[$event_4 + 4],
                        'Nit_Receptor' =>  $nodeValues[$event_4 + 5],
                        'Receptor' =>  $nodeValues[$event_4 + 6],
                    ]
                );
            }
            if (!empty($event_5) || $event_5 == 35) {
                array_push(
                    $events_list['Evento_034'],
                    [
                        'success' => "true",
                        'Evento_034' => $nodeValues[$event_5],
                        'Descripción' => $nodeValues[$event_5 + 1],
                        'Fecha' =>  $nodeValues[$event_5 + 2],
                        'Nit_Emisor' =>  $nodeValues[$event_5 + 3],
                        'Emisor' =>  $nodeValues[$event_5 + 4],
                        'Nit_Receptor' =>  $nodeValues[$event_5 + 5],
                        'Receptor' =>  $nodeValues[$event_5 + 6],
                    ]
                );
            }
            return response()->json($events_list);
        }
        else {
            return [
                    'success' => "false",
                    'message' => "Sin eventos!",
                ];
        }
    }

    public function queryeventsprefixnumber($prefix, $number)
    {
        $user = auth()->user();
        $company = $user->company;

        $invoice = Document::where('identification_number', '=', $company->identification_number)
                            ->where('prefix', '=', $prefix)
                            ->where('number', '=', $number)
                            ->where('state_document_id', '=', 1)->get();
        if(count($invoice) > 0){
            if($invoice[0]->acu_recibo == 0 && $invoice[0]->rec_bienes == 0 && $invoice[0]->aceptacion == 0 && $invoice[0]->rechazo == 0)
                return[
                    'success' => true,
                    'Aceptación Tácita' => true,
                    'send_email_success' => $invoice[0]->send_email_success == 1,
                    'send_email_date_time' => $invoice[0]->send_email_date_time,
                ];
            else
                return[
                    'success' => true,
                    'Acuse de recibo de Factura Electrónica de Venta' => $invoice[0]->acu_recibo == 1,
                    'Recibo del bien y/o prestación del servicio' => $invoice[0]->rec_bienes == 1,
                    'Aceptación expresa' => $invoice[0]->aceptacion == 1,
                    'Reclamo de la Factura Electrónica de Venta' => $invoice[0]->rechazo == 1,
                    'send_email_success' => $invoice[0]->send_email_success == 1,
                    'send_email_date_time' => $invoice[0]->send_email_date_time,
                ];
        }
        else
            return[
                'success' => false,
                'message' => 'El documento no fue encontrado en la base de datos...'
            ];
    }

    public function queryeventsuuid($uuid)
    {
        $user = auth()->user();
        $company = $user->company;

        $invoice = Document::where('cufe', '=', $uuid)->get();
        if(count($invoice) > 0){
            if($invoice[0]->acu_recibo == 0 && $invoice[0]->rec_bienes == 0 && $invoice[0]->aceptacion == 0 && $invoice[0]->rechazo == 0)
                return[
                    'success' => true,
                    'Aceptación Tácita' => true,
                    'send_email_success' => $invoice[0]->send_email_success == 1,
                    'send_email_date_time' => $invoice[0]->send_email_date_time,
                ];
            else
                return[
                    'success' => true,
                    'Acuse de recibo de Factura Electrónica de Venta' => $invoice[0]->acu_recibo == 1,
                    'Recibo del bien y/o prestación del servicio' => $invoice[0]->rec_bienes == 1,
                    'Aceptación expresa' => $invoice[0]->aceptacion == 1,
                    'Reclamo de la Factura Electrónica de Venta' => $invoice[0]->rechazo == 1,
                    'send_email_success' => $invoice[0]->send_email_success == 1,
                    'send_email_date_time' => $invoice[0]->send_email_date_time,
                ];
        }
        else
            return[
                'success' => false,
                'message' => 'El documento no fue encontrado en la base de datos...'
            ];
    }

    /**
     * Store.
     *
     * @param \App\Http\Requests\Api\SendEventRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sendevent(SendEventRequest $request, $company_idnumber = FALSE)
    {
        // User company
        if($company_idnumber){
            $company = Company::where('identification_number', $company_idnumber)->firstOrFail();
            $user = User::where('id', $company->user_id)->firstOrFail();
        }
        else{
            $user = auth()->user();
            $company = $user->company;
        }

        // Verificar la disponibilidad de la DIAN antes de continuar
        $dian_url = $company->software->url_event;
        if (!$this->verificarEstadoDIAN($dian_url)) {
            // Manejar la indisponibilidad del servicio, por ejemplo:
            return [
                'success' => false,
                'message' => 'El servicio de la DIAN no está disponible en este momento. Por favor, inténtelo más tarde.',
            ];
        }

        // Verify Certificate
        $certificate_days_left = 100;
        $c = $this->verify_certificate($user);
        if(!$c['success'])
            return $c;
        else
            $certificate_days_left = $c['certificate_days_left'];

        if($company->type_plan3->state == false)
            return [
                'success' => false,
                'message' => 'El plan en el que esta registrado la empresa se encuentra en el momento INACTIVO para enviar documentos electronicos...',
            ];

        if($company->state == false)
            return [
                'success' => false,
                'message' => 'La empresa se encuentra en el momento INACTIVA para enviar documentos electronicos...',
            ];

        if($company->type_plan3->period != 0 && $company->absolut_plan_documents == 0){
            $firstDate = new DateTime($company->start_plan_date3);
            $secondDate = new DateTime(Carbon::now()->format('Y-m-d H:i'));
            $intvl = $firstDate->diff($secondDate);
            switch($company->type_plan3->period){
                case 1:
                    if($intvl->y >= 1 || $intvl->m >= 1 || $this->qty_docs_period("RADIAN") >= $company->type_plan3->qty_docs_radian)
                        return [
                            'success' => false,
                            'message' => 'La empresa ha llegado al limite de tiempo/documentos del plan por mensualidad, por favor renueve su membresia...',
                        ];
                case 2:
                    if($intvl->y >= 1 || $this->qty_docs_period("RADIAN") >= $company->type_plan3->qty_docs_radian)
                        return [
                            'success' => false,
                            'message' => 'La empresa ha llegado al limite de tiempo/documentos del plan por anualidad, por favor renueve su membresia...',
                        ];
                case 3:
                    if($this->qty_docs_period("RADIAN") >= $company->type_plan3->qty_docs_radian)
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

        // Type document
        $typeDocument = TypeDocument::findOrFail(8);

        // Event code
        $event = Event::findOrFail($request->event_id);

        $att = new \DOMDocument;
        if(!$att->loadXML(str_replace("&", "&amp;", base64_decode($request->base64_attacheddocument))))
            return [
                'success' => false,
                'message' => "El archivo no se pudo cargar, revise los problemas asociados",
            ];
        else{
            if(!strpos($att->saveXML(), "<AttachedDocument"))
                return [
                    'success' => false,
                    'message' => "El archivo no es un AttachedDocument XML",
                ];
            if(!strpos($att->saveXML(), "<ApplicationResponse"))
                return [
                    'success' => false,
                    'message' => "En el archivo no se encontro el ApplicationResponse dentro del AttachedDocument XML",
                ];
            if(!strpos($att->saveXML(), "<Invoice") || !strpos($att->saveXML(), "</Invoice>"))
                return [
                    'success' => false,
                    'message' => "El archivo no corresponde al AttachedDocument XML de un documento Invoice ó el AttachedDocument no es valido ya que no tiene el cotenedor CDATA del Invoice",
                ];
            if(!strpos($att->saveXML(), "<ApplicationResponse") || !strpos($att->saveXML(), "</ApplicationResponse>"))
                return [
                    'success' => false,
                    'message' => "El archivo no corresponde al AttachedDocument XML de un documento valido ya que no tiene el contenedor CDATA del ApplicationResponse DIAN",
                ];

            if($request->event_id != "5")
                StorageService::ensureDirectory('received/'.$company->identification_number);

            $invoiceXMLStr = str_replace("&", "&amp;", substr(base64_decode($request->base64_attacheddocument), strpos(base64_decode($request->base64_attacheddocument), "<Invoice"), strpos(base64_decode($request->base64_attacheddocument), "/Invoice>") - strpos(base64_decode($request->base64_attacheddocument), "<Invoice") + 9));
            $invoiceXMLStr = preg_replace("/[\r\n|\n|\r]+/", "","<?xml version=\"1.0\" encoding=\"utf-8\"?>".$invoiceXMLStr);
            if($request->event_id == "5"){
                $invoice_doc = Document::where('identification_number', '=', $company->identification_number)
                                        ->where('prefix', '=', $this->getTag($invoiceXMLStr, 'Prefix', 0)->nodeValue)
                                        ->where('number', '=', $this->getTag($invoiceXMLStr, 'Prefix', 0)->nodeValue.str_replace($this->getTag($invoiceXMLStr, 'Prefix', 0)->nodeValue, "", $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/")))
                                        ->where('state_document_id', '=', 1)->first();
                if(is_null($invoice_doc)){
                    $invoice_doc = new Document();
                    $invoice_doc->identification_number = $company->identification_number;
                    $invoice_doc->request_api = NULL;
                    $invoice_doc->state_document_id = 1;
                    $invoice_doc->type_document_id = $this->getTag($invoiceXMLStr, 'InvoiceTypeCode', 0)->nodeValue;
                    if(strpos($invoiceXMLStr, "</sts:Prefix>"))
                        $invoice_doc->prefix = $this->getQuery($invoiceXMLStr, 'ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sts:DianExtensions/sts:InvoiceControl/sts:AuthorizedInvoices/sts:Prefix', 0)->nodeValue;
//                        $invoice_doc->prefix = $this->getTag($invoiceXMLStr, 'Prefix', 0)->nodeValue;
                    else
                        $invoice_doc->prefix = "";
                    $i = 0;
                    if($invoice_doc->prefix != "")
                        do{
//                            $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
                            $invoice_doc->number =  $this->getTag($invoiceXMLStr, "ID", $i)->nodeValue;
                            $i++;
                        }while(strpos($invoice_doc->number, $invoice_doc->prefix) === false);
                    else{
                        $invoice_doc->number =  $this->getQuery($invoiceXMLStr, "cbc:ID")->nodeValue;
//                        $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
                    }

                    $invoice_doc->xml = $request->base64_attacheddocument_name;
                    $invoice_doc->cufe = $this->getTag($invoiceXMLStr, 'UUID', 0)->nodeValue;
                    $invoice_doc->customer = $this->ValueXML($invoiceXMLStr, "/Invoice/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                    $invoice_doc->client_id = $invoice_doc->customer;
                    $invoice_doc->client =  "";
                    $invoice_doc->currency_id = 35;
                    $invoice_doc->date_issue = $this->getTag($invoiceXMLStr, 'IssueDate', 0)->nodeValue.' '.str_replace('-05:00', '', $this->getTag($invoiceXMLStr, 'IssueTime', 0)->nodeValue);
                    $invoice_doc->sale = $this->getTag($invoiceXMLStr, 'TaxInclusiveAmount', 0)->nodeValue;
                    if(isset($this->getTag($invoiceXMLStr, 'AllowanceTotalAmount', 0)->nodeValue))
//                        $invoice_doc->total_discount = $this->getQuery($invoiceXMLStr, "cac:LegalMonetaryTotal/cbc:AllowanceTotalAmount")->nodeValue;
                        $invoice_doc->total_discount =  $this->getTag($invoiceXMLStr, 'AllowanceTotalAmount', 0)->nodeValue;
                    else
                        $invoice_doc->total_discount = 0;
                    $invoice_doc->subtotal = $this->getTag($invoiceXMLStr, 'LineExtensionAmount', 0)->nodeValue;
                    $invoice_doc->total_tax = $invoice_doc->sale - $invoice_doc->subtotal;
                    $invoice_doc->total = $this->getTag($invoiceXMLStr, 'PayableAmount', 0)->nodeValue;
                    $invoice_doc->ambient_id = $this->getTag($invoiceXMLStr, 'ProfileExecutionID', 0)->nodeValue;
                    $invoice_doc->pdf = str_replace('.xml', '.pdf', $request->base64_attacheddocument_name);
                    $invoice_doc->aceptacion = 0;
                    $invoice_doc->taxes = NULL;
                    $invoice_doc->version_ubl_id = 2;
                    $invoice_doc->save();
                }
                $exists = Document::where('identification_number', $invoice_doc->identification_number)->where('prefix', $invoice_doc->prefix)->where('number', $invoice_doc->number)->where('state_document_id', 1)->get();
            }
            else{
                $file = fopen(StorageService::tempPath('received/'.$company->identification_number.'/'.$request->base64_attacheddocument_name), "w+");
                fwrite($file, base64_decode($request->base64_attacheddocument));
                fclose($file);

                $invoice_doc = new ReceivedDocument();
                $invoice_doc->identification_number = $this->ValueXML($invoiceXMLStr, "/Invoice/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                $invoice_doc->dv = $this->validarDigVerifDIAN($invoice_doc->identification_number);
                $invoice_doc->name_seller = $this->getTag($invoiceXMLStr, 'RegistrationName', 0)->nodeValue;
                $invoice_doc->state_document_id = 1;
                $invoice_doc->type_document_id = $this->getTag($invoiceXMLStr, 'InvoiceTypeCode', 0)->nodeValue;
                $invoice_doc->customer = $this->ValueXML($invoiceXMLStr, "/Invoice/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                if(strpos($invoiceXMLStr, "</sts:Prefix>")){
                    $invoice_doc->prefix = $this->getQuery($invoiceXMLStr, 'ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sts:DianExtensions/sts:InvoiceControl/sts:AuthorizedInvoices/sts:Prefix', 0)->nodeValue;
//                    $invoice_doc->prefix = $this->getTag($invoiceXMLStr, 'Prefix', 0)->nodeValue;
                }
                else
                    $invoice_doc->prefix = "";
                $i = 0;
                if($invoice_doc->prefix != "")
                    do{
//                        $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
                        $invoice_doc->number =  $this->getTag($invoiceXMLStr, "ID", $i)->nodeValue;
                        $i++;
                    }while(strpos($invoice_doc->number, $invoice_doc->prefix) === false);
                else{
                    $invoice_doc->number =  $this->getQuery($invoiceXMLStr, "cbc:ID")->nodeValue;
//                    $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
                }
                $invoice_doc->xml = $request->base64_attacheddocument_name;
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
                $invoice_doc->pdf = str_replace('.xml', '.pdf', $request->base64_attacheddocument_name);
                $invoice_doc->acu_recibo = 0;
                $invoice_doc->rec_bienes = 0;
                $invoice_doc->aceptacion = 0;
                $invoice_doc->rechazo = 0;

                if($invoice_doc->customer != $company->identification_number)
                    return [
                        'success' => false,
                        'message' => "El archivo no corresponde un AttachedDocument XML del adquiriente ".$company->identification_number,
                    ];
                    $exists = ReceivedDocument::where('customer', $company->identification_number)->where('identification_number', $invoice_doc->identification_number)->where('prefix', $invoice_doc->prefix)->where('number', $invoice_doc->number)->get();
            }

            // Type document - document reference
            $typeDocumentReference = TypeDocument::findOrFail($invoice_doc->type_document_id);

            if($request->resend_consecutive){
                switch($event->id){
                    case 1:
                        $invoice_doc->acu_recibo = 0;
                        if(count($exists) > 0){
                            $exists[0]->acu_recibo = 0;
                            $exists[0]->save();
                        }
                        break;
                    case 2:
                        $invoice_doc->rechazo = 0;
                        if(count($exists) > 0){
                            $exists[0]->rechazo = 0;
                            $exists[0]->save();
                        }
                        break;
                    case 3:
                        $invoice_doc->rec_bienes = 0;
                        if(count($exists) > 0){
                            $exists[0]->rec_bienes = 0;
                            $exists[0]->save();
                        }
                        break;
                    case 4:
                        $invoice_doc->aceptacion = 0;
                        if(count($exists) > 0){
                            $exists[0]->aceptacion = 0;
                            $exists[0]->save();
                        }
                        break;
                    case 4:
                        $invoice_doc->aceptacion = 0;
                        if(count($exists) > 0){
                            $exists[0]->aceptacion = 0;
                            $exists[0]->save();
                        }
                        break;
                }
            }

            if(count($exists) == 0 && $request->event_id != 5)
                $invoice_doc->save();
            else{
                switch($event->id){
                    case 1:
                        if($exists[0]->acu_recibo == 1)
                            return [
                                'success' => false,
                                'message' => "Ya se registro este evento para este documento.",
                            ];
                        break;
                    case 2:
                        if($exists[0]->rechazo == 1)
                        return [
                            'success' => false,
                            'message' => "Ya se registro este evento para este documento.",
                        ];
                        break;
                    case 3:
                        if($exists[0]->rec_bienes == 1)
                        return [
                            'success' => false,
                            'message' => "Ya se registro este evento para este documento.",
                        ];
                        break;
                    case 4:
                        if($exists[0]->aceptacion == 1)
                        return [
                            'success' => false,
                            'message' => "Ya se registro este evento para este documento.",
                        ];
                        break;
                    case 5:
                        if($exists[0]->aceptacion == 1)
                        return [
                            'success' => false,
                            'message' => "Ya se registro este evento para este documento.",
                        ];
                        break;
                }
            }
        }

        // Sender
        $senderAll = collect();
        $senderAll['name'] = $user->name;
        $senderAll['email'] = $user->email;
        $senderAll['identification_number'] = $company->identification_number;
        $senderAll['dv'] = $company->dv;
        $senderAll['tax_id'] = $company->tax_id;
        $senderAll['type_organization_id'] = $company->type_organization_id;
        $senderAll['type_regime_id'] = $company->type_regime_id;
        $senderAll['type_liability_id'] = $company->type_liability_id;
        $sender = new User($senderAll->toArray());

        // User - Sender company
        $sender->company = new Company($senderAll->toArray());

        // User - Provider

//dd($invoice_doc->name_seller);
//dd($invoice_doc->identification_number);
//dd($invoice_doc->dv);
//dd($this->ValueXML($invoiceXMLStr, "/Invoice/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cac:TaxScheme/cbc:ID/"));
//dd($this->getTag($invoiceXMLStr, 'AdditionalAccountID', 0)->nodeValue);
//dd($this->getTag($invoiceXMLStr, 'TaxLevelCode', 0, 'listName'));

        $userAll = collect();
        if($request->event_id == "5"){
            $userAll['name'] = "Unidad Administrativa Especial Dirección de Impuestos y Aduanas Nacionales";
            $userAll['identification_number'] = "800197268";
            $userAll['email'] = $user->email;
            $userAll['dv'] = "4";
            $userAll['tax_id'] = 1;
            $userAll['type_organization_id'] = 1;
            $userAll['type_regime_id'] = 1;
            $userAll['type_liability_id'] = 117;
        }
        else{
            $userAll['name'] = $invoice_doc->name_seller;
            $userAll['identification_number'] = $invoice_doc->identification_number;
            $userAll['email'] = $this->ValueXML($invoiceXMLStr, "/Invoice/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail/");
            $userAll['dv'] = $invoice_doc->dv;
            $userAll['tax_id'] = 1;
            $userAll['type_organization_id'] = TypeOrganization::where('code', 'like', $this->getTag($invoiceXMLStr, 'AdditionalAccountID', 0)->nodeValue.'%')->firstOrFail()->id;
            if(count(TypeRegime::where('code', 'like', $this->getTag($invoiceXMLStr, 'TaxLevelCode', 0, 'listName').'%')->get()))
                $userAll['type_regime_id'] = TypeRegime::where('code', 'like', $this->getTag($invoiceXMLStr, 'TaxLevelCode', 0, 'listName').'%')->firstOrFail()->id;
            else
                $userAll['type_regime_id'] = 1;
            $userAll['type_liability_id'] = 117;
        }
        $user = new User($userAll->toArray());

        // User - Provider company
        $user->company = new Company($userAll->toArray());

        // Document reference
        $documentReferenceAll = collect();
        if($request->event_id == "5")
            $documentReferenceAll['number'] = $invoice_doc->number;
        else
            $documentReferenceAll['number'] = $invoice_doc->number;
        $documentReferenceAll['prefix'] = $invoice_doc->prefix;
        $documentReferenceAll['uuid'] = $invoice_doc->cufe;
        $documentReferenceAll['type_document_id'] = $invoice_doc->type_document_id;
        $documentReference = new DocumentReference($documentReferenceAll->toArray());

        // Issuer Party
        if($request->issuer_party)
            $issuerparty = new IssuerParty($request->issuer_party);
        else
            $issuerparty = NULL;

        // Rejection Id
        if($request->type_rejection_id)
            $typerejection = TypeRejection::where('id', $request->type_rejection_id)->firstOrFail();
        else
            $typerejection = NULL;

        if($request->event_id == "5"){
            $customer_info = Customer::where('identification_number', $invoice_doc->customer)->first();
            if($customer_info){
                $customer_name = $customer_info->name;
                $customer_id = $customer_info->identification_number;
            } else {
                $customer_name = $invoice_doc->client ?? $invoice_doc->customer;
                $customer_id = $invoice_doc->customer;
            }
            $notes = "Manifiesto bajo la gravedad de juramento que transcurridos 3 días hábiles contados desde la creación del Recibo de bienes y servicios, el adquirente {$customer_name} identificado con NIT {$customer_id} no manifestó expresamente la aceptación o rechazo de la referida factura, ni reclamó en contra de su contenido.";
        }
        else
            $notes = NULL;

        // Create XML
        $eventXML = $this->createXML(compact('user', 'company', 'typeDocument', 'event', 'sender', 'documentReference', 'typeDocumentReference', 'issuerparty', 'typerejection', 'notes', 'request'));

        // Signature XML
        $signEvent = new SignEvent($company->certificate->path, $company->certificate->password);
        $signEvent->softwareID = $company->software->identifier;
        $signEvent->pin = $company->software->pin;

        if ($request->GuardarEn){
            if (!is_dir($request->GuardarEn)) {
                mkdir($request->GuardarEn);
            }
        }
        else{
            StorageService::ensureDirectory("public/{$company->identification_number}");
        }
        if ($request->GuardarEn)
            $signEvent->GuardarEn = $request->GuardarEn."\\EV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml";
        else
            $signEvent->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/EV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml");

        $sendEvent = new SendEvent($company->certificate->path, $company->certificate->password);
        $sendEvent->To = $company->software->url_event;

        if ($request->GuardarEn){
            $sendEvent->contentFile = $this->zipBase64SendEvent($company, $event->code, $sender->company->identification_number, $documentReference->getPrefixAttribute().$documentReference->getNumberAttribute(), $signEvent->sign($eventXML), $request->GuardarEn."\\EVS-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}");
            $filename = "EVS-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}";
        }
        else{
            $sendEvent->contentFile = $this->zipBase64SendEvent($company, $event->code, $sender->company->identification_number, $documentReference->getPrefixAttribute().$documentReference->getNumberAttribute(), $signEvent->sign($eventXML), StorageService::tempPath("public/{$company->identification_number}/EVS-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}"));
            $filename = "EVS-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}";
        }

        $QRStr = $this->createPDFEvent($user, $company, $typeDocument, $event, $sender, $documentReference, $typeDocumentReference, $issuerparty, $typerejection, $notes, $request, $signEvent->ConsultarCUDEEVENT());

        if ($request->GuardarEn){
            try{
                $respuestadian = $sendEvent->signToSend($request->GuardarEn."\\{$company->identification_number}\\ReqEV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml")->getResponseToObject($request->GuardarEn."\\{$company->identification_number}\\RptaEV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml");
                $r = [
                    'success' => true,
                    'message' => "{$typeDocument->name} #{$documentReference->getPrefixAttribute()}{$documentReference->getNumberAttribute()} generada con éxito",
                    'ResponseDian' => $respuestadian,
                    'cude' => $signEvent->ConsultarCUDEEVENT(),
                    'certificate_days_left' => $certificate_days_left,
                ];
                if(isset($respuestadian->html))
                    return [
                        'success' => false,
                        'message' => "El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde..."
                    ];

                if($respuestadian->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->IsValid == 'true'){
                    if($request->event_id == "5"){
                        $invoice = Document::where('identification_number', '=', $company->identification_number)
                                            ->where('prefix', '=', $documentReference->prefix)
                                            ->where('number', '=', $documentReference->number)
                                            ->where('state_document_id', '=', 1)->get();
                        $r = array_merge($r, array('transmitter_id' => $invoice[0]->identification_number,
                                                   'transmitter_name' => $sender->name,
                                                   'receiver_id' => $invoice[0]->customer,
                                                   'receiver_name' => $customer_info->name,
                                                   'invoice_number' => $invoice[0]->number,
                                                   'invoice_cufe' => $invoice[0]->date_issue,
                                                   'invoice_total' => $invoice[0]->total,
                                                   'invoice_tax' => $invoice[0]->total_tax));
                    }
                    else{
                        $invoice = ReceivedDocument::where('identification_number', '=', $user->company->identification_number)
                                                    ->where('customer', '=', $sender->company->identification_number)
                                                    ->where('prefix', '=', $documentReference->prefix)
                                                    ->where('number', '=', $documentReference->number)
                                                    ->where('state_document_id', '=', 1)->get();
                        $r = array_merge($r, array('transmitter_id' => $invoice[0]->customer,
                                                   'transmitter_name' => $sender->name,
                                                   'receiver_id' => $invoice[0]->identification_number,
                                                   'receiver_name' => $invoice[0]->name_seller,
                                                   'invoice_number' => $invoice[0]->number,
                                                   'invoice_cufe' => $invoice[0]->date_issue,
                                                   'invoice_total' => $invoice[0]->total,
                                                   'invoice_tax' => $invoice[0]->total_tax));
                    }
                    if(count($invoice) > 0){
                        switch($event->id){
                            case 1:
                                $invoice[0]->acu_recibo = 1;
                                $invoice[0]->cude_acu_recibo = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_acu_recibo = json_encode($r);
                                break;
                            case 2:
                                $invoice[0]->rechazo = 1;
                                $invoice[0]->cude_rechazo = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_rechazo = json_encode($r);
                                break;
                            case 3:
                                $invoice[0]->rec_bienes = 1;
                                $invoice[0]->cude_rec_bienes = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_rec_bienes = json_encode($r);
                                break;
                            case 4:
                                $invoice[0]->aceptacion = 1;
                                $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_aceptacion = json_encode($r);
                                break;
                            case 5:
                                $invoice[0]->aceptacion = 1;
                                $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_aceptacion = json_encode($r);
                                break;
                            }
                        $invoice[0]->save();

                        if(isset($request->sendmail)){
                            if($request->sendmail){
                                if(count($invoice) > 0){
                                    try{
                                        Mail::to($user->email)->send(new EventMail($invoice, $sender, $user, $event, $request, $filename));
                                        if($request->sendmailtome)
                                            Mail::to($sender->email)->send(new EventMail($invoice, $sender, $user,  $event, $request, $filename));
                                        if($request->email_cc_list){
                                            foreach($request->email_cc_list as $email)
                                                Mail::to($email)->send(new EventMail($invoice, $sender, $user,  $event, $request, $filename));
                                        }
                                    } catch (\Exception $m) {
                                        \Log::debug($m->getMessage());
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian)),
                ];
            }
            return $r;
        }
        else{
            try{
                $respuestadian = $sendEvent->signToSend(StorageService::tempPath("public/{$company->identification_number}/ReqEV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml"))->getResponseToObject(StorageService::tempPath("public/{$company->identification_number}/RptaEV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml"));
                $r = [
                    'success' => true,
                    'message' => "{$typeDocument->name} #{$documentReference->getPrefixAttribute()}{$documentReference->getNumberAttribute()} generada con éxito",
                    'ResponseDian' => $respuestadian,
                    'cude' => $signEvent->ConsultarCUDEEVENT(),
                    'certificate_days_left' => $certificate_days_left,
                ];
                if(isset($respuestadian->html))
                    return [
                        'success' => false,
                        'message' => "El servicio DIAN no se encuentra disponible en el momento, reintente mas tarde..."
                    ];

                if($respuestadian->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->IsValid == 'true'){
                    if($request->event_id == "5"){
                        $invoice = Document::where('identification_number', '=', $company->identification_number)
                                            ->where('prefix', '=', $documentReference->prefix)
                                            ->where('number', '=', $documentReference->number)
                                            ->where('state_document_id', '=', 1)->get();
                        $r = array_merge($r, array('transmitter_id' => $invoice[0]->identification_number,
                                                   'transmitter_name' => $sender->name,
                                                   'receiver_id' => $invoice[0]->customer,
                                                   'receiver_name' => $customer_info->name,
                                                   'invoice_number' => $invoice[0]->number,
                                                   'invoice_date' => $invoice[0]->date_issue,
                                                   'invoice_cufe' => $invoice[0]->cufe,
                                                   'invoice_total' => $invoice[0]->total,
                                                   'invoice_tax' => $invoice[0]->total_tax));
                    }
                    else{
                        $invoice = ReceivedDocument::where('identification_number', '=', $user->company->identification_number)
                                                    ->where('customer', '=', $sender->company->identification_number)
                                                    ->where('prefix', '=', $documentReference->prefix)
                                                    ->where('number', '=', $documentReference->number)
                                                    ->where('state_document_id', '=', 1)->get();
                        $r = array_merge($r, array('transmitter_id' => $invoice[0]->customer,
                                                   'transmitter_name' => $sender->name,
                                                   'receiver_id' => $invoice[0]->identification_number,
                                                   'receiver_name' => $invoice[0]->name_seller,
                                                   'invoice_number' => $invoice[0]->number,
                                                   'invoice_date' => $invoice[0]->date_issue,
                                                   'invoice_cufe' => $invoice[0]->cufe,
                                                   'invoice_total' => $invoice[0]->total,
                                                   'invoice_tax' => $invoice[0]->total_tax));
                    }
                    if(count($invoice) > 0){
                        switch($event->id){
                            case 1:
                                $invoice[0]->acu_recibo = 1;
                                $invoice[0]->cude_acu_recibo = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_acu_recibo = json_encode($r);
                                break;
                            case 2:
                                $invoice[0]->rechazo = 1;
                                $invoice[0]->cude_rechazo = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_rechazo = json_encode($r);
                                break;
                            case 3:
                                $invoice[0]->rec_bienes = 1;
                                $invoice[0]->cude_rec_bienes = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_rec_bienes = json_encode($r);
                                break;
                            case 4:
                                $invoice[0]->aceptacion = 1;
                                $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_aceptacion = json_encode($r);
                                break;
                            case 5:
                                $invoice[0]->aceptacion = 1;
                                $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_aceptacion = json_encode($r);
                                break;
                        }
                        $invoice[0]->save();

                        if(isset($request->sendmail)){
                            if($request->sendmail){
                                if(count($invoice) > 0){
                                    try{
                                        Mail::to($user->email)->send(new EventMail($invoice, $sender, $user, $event, $request, $filename));
                                        if($request->sendmailtome)
                                            Mail::to($sender->email)->send(new EventMail($invoice, $sender, $user,  $event, $request, $filename));
                                        if($request->email_cc_list){
                                            foreach($request->email_cc_list as $email)
                                                Mail::to($email)->send(new EventMail($invoice, $sender, $user,  $event, $request, $filename));
                                        }
                                    } catch (\Exception $m) {
                                        \Log::debug($m->getMessage());
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    if($respuestadian->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->ErrorMessage->string === "Regla: 90, Rechazo: Documento procesado anteriormente."){
                       if($request->event_id == "5")
                            $invoice = Document::where('identification_number', '=', $company->identification_number)
                                                ->where('prefix', '=', $documentReference->prefix)
                                                ->where('number', '=', $documentReference->number)
                                                ->where('state_document_id', '=', 1)->get();
                        else
                            $invoice = ReceivedDocument::where('identification_number', '=', $user->company->identification_number)
                                                        ->where('customer', '=', $sender->company->identification_number)
                                                        ->where('prefix', '=', $documentReference->prefix)
                                                        ->where('number', '=', $documentReference->number)
                                                        ->where('state_document_id', '=', 1)->get();
                        if(count($invoice) > 0){
                            switch($event->id){
                                case 1:
                                    $invoice[0]->acu_recibo = 1;
                                    if(is_null($invoice[0]->cude_acu_recibo) || $invoice[0]->cude_acu_recibo == ""){
                                        $invoice[0]->cude_acu_recibo = $signEvent->ConsultarCUDEEVENT();
                                        $invoice[0]->payload_acu_recibo = json_encode($r);
                                    }
                                    break;
                                case 2:
                                    $invoice[0]->rechazo = 1;
                                    if(is_null($invoice[0]->cude_rechazo) || $invoice[0]->cude_rechazo == ""){
                                        $invoice[0]->cude_rechazo = $signEvent->ConsultarCUDEEVENT();
                                        $invoice[0]->payload_rechazo = json_encode($r);
                                    }
                                    break;
                                case 3:
                                    $invoice[0]->rec_bienes = 1;
                                    if(is_null($invoice[0]->cude_rec_bienes) || $invoice[0]->cude_rec_bienes == ""){
                                        $invoice[0]->cude_rec_bienes = $signEvent->ConsultarCUDEEVENT();
                                        $invoice[0]->payload_rec_bienes = json_encode($r);
                                    }
                                    break;
                                case 4:
                                    $invoice[0]->aceptacion = 1;
                                    if(is_null($invoice[0]->cude_aceptacion) || $invoice[0]->cude_aceptacion == ""){
                                        $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                        $invoice[0]->payload_aceptacion = json_encode($r);
                                    }
                                    break;
                                case 5:
                                    $invoice[0]->aceptacion = 1;
                                    if(is_null($invoice[0]->cude_aceptacion) || $invoice[0]->cude_aceptacion == ""){
                                        $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                        $invoice[0]->payload_aceptacion = json_encode($r);
                                    }
                                    break;
                            }
                            $invoice[0]->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian)),
                ];
            }
            return $r;
        }
    }

    /**
     * Send Event Data.
     *
     * @param \App\Http\Requests\Api\SendEventRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sendeventdata(SendEventDataRequest $request, $company_idnumber = FALSE)
    {
        // User company
        if($company_idnumber){
            $company = Company::where('identification_number', $company_idnumber)->firstOrFail();
            $user = User::where('id', $company->user_id)->firstOrFail();
        }
        else{
            $user = auth()->user();
            $company = $user->company;
        }

        // Verificar la disponibilidad de la DIAN antes de continuar
        $dian_url = $company->software->url_event;
        if (!$this->verificarEstadoDIAN($dian_url)) {
            // Manejar la indisponibilidad del servicio, por ejemplo:
            return [
                'success' => false,
                'message' => 'El servicio de la DIAN no está disponible en este momento. Por favor, inténtelo más tarde.',
            ];
        }

        // Verify Certificate
        $certificate_days_left = 0;
        $c = $this->verify_certificate($user);
        if(!$c['success'])
            return $c;
        else
            $certificate_days_left = $c['certificate_days_left'];

        $request->type_document_id = 8;

        if($company->type_plan3->state == false)
            return [
                'success' => false,
                'message' => 'El plan en el que esta registrado la empresa se encuentra en el momento INACTIVO para enviar documentos electronicos...',
            ];

        if($company->state == false)
            return [
                'success' => false,
                'message' => 'La empresa se encuentra en el momento INACTIVA para enviar documentos electronicos...',
            ];

        if($company->type_plan3->period != 0 && $company->absolut_plan_documents == 0){
            $firstDate = new DateTime($company->start_plan_date3);
            $secondDate = new DateTime(Carbon::now()->format('Y-m-d H:i'));
            $intvl = $firstDate->diff($secondDate);
            switch($company->type_plan3->period){
                case 1:
                    if($intvl->y >= 1 || $intvl->m >= 1 || $this->qty_docs_period("RADIAN") >= $company->type_plan3->qty_docs_radian)
                        return [
                            'success' => false,
                            'message' => 'La empresa ha llegado al limite de tiempo/documentos del plan por mensualidad, por favor renueve su membresia...',
                        ];
                case 2:
                    if($intvl->y >= 1 || $this->qty_docs_period("RADIAN") >= $company->type_plan3->qty_docs_radian)
                        return [
                            'success' => false,
                            'message' => 'La empresa ha llegado al limite de tiempo/documentos del plan por anualidad, por favor renueve su membresia...',
                        ];
                case 3:
                    if($this->qty_docs_period("RADIAN") >= $company->type_plan3->qty_docs_radian)
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

        // Type document
        $typeDocument = TypeDocument::findOrFail(8);

        // Event code
        $event = Event::findOrFail($request->event_id);

        $xmlDIAN = new XmlDocumentController();
        $send = [
            'is_event' => true,
        ];
        $data_send = json_encode($send);
        $r = new XmlDocumentRequest($send);
        $r = $xmlDIAN->document($r, $request->document_reference['cufe'], false, $company);
        if($r['success'])
            $invoiceXMLStr = base64_decode(json_encode($r['ResponseDian']->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->XmlBytesBase64));
        else
            return [
                'success' => false,
                'message' => $r['ResponseDian'],
            ];

        if(strpos($invoiceXMLStr, "<Invoice") === 0 &&  strpos($invoiceXMLStr, "</Invoice>") === 0)
            return [
                'success' => false,
                'message' => "El CUFE ingresado no corresponde al XML de un documento Invoice ....",
            ];

        if(strpos($invoiceXMLStr, "</sts:Prefix>"))
            $prefix = $this->getQuery($invoiceXMLStr, 'ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sts:DianExtensions/sts:InvoiceControl/sts:AuthorizedInvoices/sts:Prefix', 0)->nodeValue;
//                            $invoice_doc->prefix = $this->getTag($invoiceXMLStr, 'Prefix', 0)->nodeValue;
        else
            $prefix = "";
        $i = 0;
        if($prefix != "")
            do{
//                             $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
                $number =  $this->getTag($invoiceXMLStr, "ID", $i)->nodeValue;
                $i++;
            }while(strpos($number, $prefix) === false);
        else{
            $number =  $this->getQuery($invoiceXMLStr, "cbc:ID")->nodeValue;
//                            $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
        }

        if($request->event_id == "5"){
            $invoice_doc = Document::where('identification_number', '=', $company->identification_number)
                                   ->where('prefix', '=', $prefix)
                                   ->where('number', '=', substr($number, strpos($number, $prefix) + strlen($prefix)))
                                   ->where('state_document_id', '=', 1)->first();
            if(is_null($invoice_doc)){
//                $invoice_doc = new Document();
//                $invoice_doc->identification_number = $company->identification_number;
//                $invoice_doc->dv = $this->validarDigVerifDIAN($invoice_doc->identification_number);
//                $invoice_doc->request_api = NULL;
//                $invoice_doc->state_document_id = 1;
//                $invoice_doc->type_document_id = $request->document_reference['type_document_id'];
//                if(isset($request->document_reference['prefix']))
//                    $invoice_doc->prefix = $request->document_reference['prefix'];
//                else
//                    $invoice_doc->prefix = "";
//
//                $invoice_doc->number = $request->document_reference['number'];
//                $invoice_doc->xml = "no-attached-document";
//                $invoice_doc->cufe = $request->document_reference['cufe'];
//                $invoice_doc->customer = $request->seller['identification_number'];
//                $invoice_doc->client_id = $invoice_doc->customer;
//                $invoice_doc->client =  "";
//                $invoice_doc->currency_id = 35;
//                $invoice_doc->date_issue = $request->document_reference['issue_date'] ? $request->document_reference['issue_date'] : Carbon::now()->format('Y-m-d H:i:s');
//                $invoice_doc->sale = $request->document_reference['total_sale'] ? $request->document_reference['total_sale'] : 0;
//                $invoice_doc->total_discount =  $request->document_reference['allowance_amount'] ? $request->document_reference['allowance_amount'] : 0;
//                $invoice_doc->total_tax = $request->document_reference['tax_amount'] ? $request->document_reference['tax_amount'] : 0;
//                $invoice_doc->subtotal = $invoice_doc->sale-$invoice_doc->total_tax;
//                $invoice_doc->total = $invoice_doc->sale;
//                $invoice_doc->ambient_id = 1;
//                $invoice_doc->pdf = "no-attached-document";
//                $invoice_doc->aceptacion = 0;
//                $invoice_doc->taxes = NULL;
//                $invoice_doc->version_ubl_id = 2;
//                $invoice_doc->save();
                $invoice_doc = new Document();
                $invoice_doc->identification_number = $company->identification_number;
                $invoice_doc->request_api = NULL;
                $invoice_doc->state_document_id = 1;
                $invoice_doc->type_document_id = $this->getTag($invoiceXMLStr, 'InvoiceTypeCode', 0)->nodeValue;
                if(strpos($invoiceXMLStr, "</sts:Prefix>")){
                    $invoice_doc->prefix = $this->getQuery($invoiceXMLStr, 'ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sts:DianExtensions/sts:InvoiceControl/sts:AuthorizedInvoices/sts:Prefix', 0)->nodeValue;
//                    $invoice_doc->prefix = $this->getTag($invoiceXMLStr, 'Prefix', 0)->nodeValue;
                }
                else
                    $invoice_doc->prefix = "";
                $i = 0;
                if($invoice_doc->prefix != "")
                    do{
//                        $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
                        $invoice_doc->number =  $this->getTag($invoiceXMLStr, "ID", $i)->nodeValue;
                        $i++;
                    }while(strpos($invoice_doc->number, $invoice_doc->prefix) === false);
                else{
                    $invoice_doc->number =  $this->getQuery($invoiceXMLStr, "cbc:ID")->nodeValue;
//                    $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
                }

                $invoice_doc->xml = "no-attached-document";
                $invoice_doc->cufe = $this->getTag($invoiceXMLStr, 'UUID', 0)->nodeValue;
                $invoice_doc->customer = $this->getQuery($invoiceXMLStr, "cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID")->nodeValue;
                $invoice_doc->client_id = $invoice_doc->customer;
                $invoice_doc->client =  "";
                $invoice_doc->currency_id = 35;
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
                $invoice_doc->pdf = "no-attached-document";
                $invoice_doc->aceptacion = 0;
                $invoice_doc->taxes = NULL;
                $invoice_doc->version_ubl_id = 2;
                if($company->establishments > 0)
                    $invoice_doc->establishment_id = $e[0]->id;
                $invoice_doc->save();
            }
            $exists = Document::where('identification_number', $invoice_doc->identification_number)->where('prefix', $invoice_doc->prefix)->where('number', $invoice_doc->number)->where('state_document_id', 1)->get();
        }
        else{
            $invoice_doc = new ReceivedDocument();
//            $invoice_doc->identification_number = $request->seller['identification_number'];
//            $invoice_doc->dv = $this->validarDigVerifDIAN($invoice_doc->identification_number);
//            $invoice_doc->name_seller = $request->seller['name'];
//            $invoice_doc->state_document_id = 1;
//            $invoice_doc->type_document_id = $request->document_reference['type_document_id'];
//            $invoice_doc->customer = $company->identification_number;
//            if(isset($request->document_reference['prefix']))
//                $invoice_doc->prefix = $request->document_reference['prefix'];
//            else
//                $invoice_doc->prefix = "";

//            if(isset($request->document_reference['prefix']))
//                $invoice_doc->number = $request->document_reference['prefix'].$request->document_reference['number'];
//            else
//                $invoice_doc->number = $request->document_reference['number'];
//            $invoice_doc->xml = 'no-attached-document';
//            $invoice_doc->cufe = $request->document_reference['cufe'];
//            $invoice_doc->date_issue = isset($request->document_reference['issue_date']) ? $request->document_reference['issue_date'] : Carbon::now()->format('Y-m-d H:i:s');
//            $invoice_doc->sale = isset($request->document_reference['total_sale']) ? $request->document_reference['total_sale'] : 0;
//            $invoice_doc->total_discount =  isset($request->document_reference['allowance_amount']) ? $request->document_reference['allowance_amount'] : 0;
//            $invoice_doc->total_tax = isset($request->document_reference['tax_amount']) ? $request->document_reference['tax_amount'] : 0;
//            $invoice_doc->subtotal = $invoice_doc->sale-$invoice_doc->total_tax;
//            $invoice_doc->total = $invoice_doc->sale;
//            $invoice_doc->ambient_id = 1;
//            $invoice_doc->pdf = "no-attached-document";
//            $invoice_doc->acu_recibo = 0;
//            $invoice_doc->rec_bienes = 0;
//            $invoice_doc->aceptacion = 0;
//            $invoice_doc->rechazo = 0;

            $invoice_doc->identification_number = $this->getQuery($invoiceXMLStr, "cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID")->nodeValue;
            $invoice_doc->dv = $this->validarDigVerifDIAN($invoice_doc->identification_number);
            $invoice_doc->name_seller = $this->getTag($invoiceXMLStr, 'RegistrationName', 0)->nodeValue;
            $invoice_doc->state_document_id = 1;
            $invoice_doc->type_document_id = $this->getTag($invoiceXMLStr, 'InvoiceTypeCode', 0)->nodeValue;
            $invoice_doc->customer = $this->getQuery($invoiceXMLStr, "cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID")->nodeValue;
            $invoice_doc->customer_name = $this->getQuery($invoiceXMLStr, "cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:RegistrationName")->nodeValue ?? $this->getQuery($invoiceXMLStr, "cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name")->nodeValue;
            if(strpos($invoiceXMLStr, "</sts:Prefix>"))
                $invoice_doc->prefix = $this->getQuery($invoiceXMLStr, 'ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sts:DianExtensions/sts:InvoiceControl/sts:AuthorizedInvoices/sts:Prefix', 0)->nodeValue;
//                                $invoice_doc->prefix = $this->getTag($invoiceXMLStr, 'Prefix', 0)->nodeValue;
            else
                $invoice_doc->prefix = "";
            $i = 0;
            if($invoice_doc->prefix != "")
                do{
//                                 $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
                    $invoice_doc->number =  $this->getTag($invoiceXMLStr, "ID", $i)->nodeValue;
                    $i++;
                }while(strpos($invoice_doc->number, $invoice_doc->prefix) === false);
            else{
                $invoice_doc->number =  $this->getQuery($invoiceXMLStr, "cbc:ID")->nodeValue;
//                                $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
            }

            $invoice_doc->xml = "no-attached-document";
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
            $invoice_doc->pdf = "no-attached-document";
            $invoice_doc->acu_recibo = 0;
            $invoice_doc->rec_bienes = 0;
            $invoice_doc->aceptacion = 0;
            $invoice_doc->rechazo = 0;

            if($invoice_doc->customer != $company->identification_number)
                return [
                    'success' => false,
                    'message' => "El adquiriente no corresponde a la empresa con nit: ".$company->identification_number,
                ];

            $exists = ReceivedDocument::where('customer', $company->identification_number)->where('identification_number', $invoice_doc->identification_number)->where('prefix', $invoice_doc->prefix)->where('number', $invoice_doc->number)->get();
        }

        // Type document - document reference
        $typeDocumentReference = TypeDocument::findOrFail($invoice_doc->type_document_id);

        if($request->resend_consecutive){
            switch($event->id){
                case 1:
                    $invoice_doc->acu_recibo = 0;
                    if(count($exists) > 0){
                        $exists[0]->acu_recibo = 0;
                        $exists[0]->save();
                    }
                    break;
                case 2:
                    $invoice_doc->rechazo = 0;
                    if(count($exists) > 0){
                        $exists[0]->rechazo = 0;
                        $exists[0]->save();
                    }
                    break;
                case 3:
                    $invoice_doc->rec_bienes = 0;
                    if(count($exists) > 0){
                        $exists[0]->rec_bienes = 0;
                        $exists[0]->save();
                    }
                    break;
                case 4:
                    $invoice_doc->aceptacion = 0;
                    if(count($exists) > 0){
                        $exists[0]->aceptacion = 0;
                        $exists[0]->save();
                    }
                    break;
                case 4:
                    $invoice_doc->aceptacion = 0;
                    if(count($exists) > 0){
                        $exists[0]->aceptacion = 0;
                        $exists[0]->save();
                    }
                    break;
            }
        }

        if(count($exists) == 0 && $request->event_id != 5)
            $invoice_doc->save();
        else{
            switch($event->id){
                case 1:
                    if($exists[0]->acu_recibo == 1){
                        return [
                            'success' => false,
                            'message' => "Ya se registro este evento para este documento.",
                            'cude' => $exists[0]->cude_acu_recibo,
                            'date_issue' => $exists[0]->date_issue,
                            'seller_id' => $exists[0]->identification_number,
                            'seller_name' => $exists[0]->name_seller,
                            'invoice_number' => $exists[0]->prefix.' - '.$exists[0]->number,
                            'invoice_cufe' => $exists[0]->cufe,
                        ];
                    }
                    break;
                case 2:
                    if($exists[0]->rechazo == 1){
                        return [
                            'success' => false,
                            'message' => "Ya se registro este evento para este documento.",
                            'cude' => $exists[0]->cude_rechazo,
                            'date_issue' => $exists[0]->date_issue,
                            'seller_id' => $exists[0]->identification_number,
                            'seller_name' => $exists[0]->name_seller,
                            'invoice_number' => $exists[0]->prefix.' - '.$exists[0]->number,
                            'invoice_cufe' => $exists[0]->cufe,
                        ];
                    }
                    break;
                case 3:
                    if($exists[0]->rec_bienes == 1){
                        return [
                            'success' => false,
                            'message' => "Ya se registro este evento para este documento.",
                            'cude' => $exists[0]->cude_rec_bienes,
                            'date_issue' => $exists[0]->date_issue,
                            'seller_id' => $exists[0]->identification_number,
                            'seller_name' => $exists[0]->name_seller,
                            'invoice_number' => $exists[0]->prefix.' - '.$exists[0]->number,
                            'invoice_cufe' => $exists[0]->cufe,
                        ];
                    }
                    break;
                case 4:
                    if($exists[0]->aceptacion == 1){
                        return [
                            'success' => false,
                            'message' => "Ya se registro este evento para este documento.",
                            'cude' => $exists[0]->cude_aceptacion,
                            'date_issue' => $exists[0]->date_issue,
                            'seller_id' => $exists[0]->identification_number,
                            'seller_name' => $exists[0]->name_seller,
                            'invoice_number' => $exists[0]->prefix.' - '.$exists[0]->number,
                            'invoice_cufe' => $exists[0]->cufe,
                        ];
                    }
                    break;
                case 5:
                    if($exists[0]->aceptacion == 1){
                        return [
                            'success' => false,
                            'message' => "Ya se registro este evento para este documento.",
                            'cude' => $exists[0]->cude_aceptacion,
                            'date_issue' => $exists[0]->date_issue,
                            'seller_id' => $exists[0]->identification_number,
                            'seller_name' => $exists[0]->name_seller,
                            'invoice_number' => $exists[0]->prefix.' - '.$exists[0]->number,
                            'invoice_cufe' => $exists[0]->cufe,
                        ];
                    }
                    break;
            }
        }

        // Sender
        $senderAll = collect();
        $senderAll['name'] = $user->name;
        $senderAll['email'] = $user->email;
        $senderAll['identification_number'] = $company->identification_number;
        $senderAll['dv'] = $company->dv;
        $senderAll['tax_id'] = $company->tax_id;
        $senderAll['type_organization_id'] = $company->type_organization_id;
        $senderAll['type_regime_id'] = $company->type_regime_id;
        $senderAll['type_liability_id'] = $company->type_liability_id;
        $sender = new User($senderAll->toArray());

        // User - Sender company
        $sender->company = new Company($senderAll->toArray());

        // User - Provider

        $userAll = collect();
        if($request->event_id == "5"){
            $userAll['name'] = "Unidad Administrativa Especial Dirección de Impuestos y Aduanas Nacionales";
            $userAll['identification_number'] = "800197268";
            $userAll['email'] = $user->email;
            $userAll['dv'] = "4";
            $userAll['tax_id'] = 1;
            $userAll['type_organization_id'] = 1;
            $userAll['type_regime_id'] = 1;
            $userAll['type_liability_id'] = 117;
        }
        else{
//            $userAll['name'] = $invoice_doc->name_seller;
//            $userAll['identification_number'] = $invoice_doc->identification_number;
//            $userAll['email'] = $request->seller['email'];
//            $userAll['dv'] = $invoice_doc->dv;
//            $userAll['tax_id'] = 1;
//            $userAll['type_organization_id'] = $request->seller['type_organization_id'];
//            $userAll['type_regime_id'] = $request->seller['type_regime_id'];
//            $userAll['type_liability_id'] = $request->seller['type_liability_id'];;
            $userAll['name'] = $invoice_doc->name_seller;
            $userAll['identification_number'] = $invoice_doc->identification_number;
            $userAll['email'] = $this->ValueXML($invoiceXMLStr, "/Invoice/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail/");
            $userAll['dv'] = $invoice_doc->dv;
            $userAll['tax_id'] = 1;
            $userAll['type_organization_id'] = TypeOrganization::where('code', 'like', $this->getTag($invoiceXMLStr, 'AdditionalAccountID', 0)->nodeValue.'%')->firstOrFail()->id;
            if(count(TypeRegime::where('code', 'like', $this->getTag($invoiceXMLStr, 'TaxLevelCode', 0, 'listName').'%')->get()))
                $userAll['type_regime_id'] = TypeRegime::where('code', 'like', $this->getTag($invoiceXMLStr, 'TaxLevelCode', 0, 'listName').'%')->firstOrFail()->id;
            else
                $userAll['type_regime_id'] = 1;
            $userAll['type_liability_id'] = 117;
        }
        $user = new User($userAll->toArray());

        // User - Provider company
        $user->company = new Company($userAll->toArray());

        // Document reference
        $documentReferenceAll = collect();
        if($request->event_id == "5")
            $documentReferenceAll['number'] = $invoice_doc->prefix.$invoice_doc->number;
        else
            $documentReferenceAll['number'] = $invoice_doc->number;
        $documentReferenceAll['prefix'] = $invoice_doc->prefix;
        $documentReferenceAll['uuid'] = $invoice_doc->cufe;
        $documentReferenceAll['type_document_id'] = $invoice_doc->type_document_id;
        $documentReference = new DocumentReference($documentReferenceAll->toArray());

        // Issuer Party
        if($request->issuer_party)
            $issuerparty = new IssuerParty($request->issuer_party);
        else
            $issuerparty = NULL;

        // Rejection Id
        if($request->type_rejection_id)
            $typerejection = TypeRejection::where('id', $request->type_rejection_id)->firstOrFail();
        else
            $typerejection = NULL;

        if($request->event_id == "5"){
            $customer_info = Customer::where('identification_number', $invoice_doc->customer)->first();
            if($customer_info){
                $customer_name = $customer_info->name;
                $customer_id = $customer_info->identification_number;
            } else {
                $customer_name = $invoice_doc->client ?? $invoice_doc->customer;
                $customer_id = $invoice_doc->customer;
            }
            $notes = "Manifiesto bajo la gravedad de juramento que transcurridos 3 días hábiles contados desde la creación del Recibo de bienes y servicios, el adquirente {$customer_name} identificado con NIT {$customer_id} no manifestó expresamente la aceptación o rechazo de la referida factura, ni reclamó en contra de su contenido.";
        }
        else
            $notes = NULL;
        $is_event = true;        
        // Create XML
        $eventXML = $this->createXML(compact('user', 'company', 'typeDocument', 'event', 'sender', 'documentReference', 'typeDocumentReference', 'issuerparty', 'typerejection', 'notes', 'request', 'is_event'));

        // Signature XML
        $signEvent = new SignEvent($company->certificate->path, $company->certificate->password);
        $signEvent->softwareID = $company->software->identifier;
        $signEvent->pin = $company->software->pin;

        if ($request->GuardarEn){
            if (!is_dir($request->GuardarEn)) {
                mkdir($request->GuardarEn);
            }
        }
        else{
            StorageService::ensureDirectory("public/{$company->identification_number}");
        }
        if ($request->GuardarEn)
            $signEvent->GuardarEn = $request->GuardarEn."\\EV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml";
        else
            $signEvent->GuardarEn = StorageService::tempPath("public/{$company->identification_number}/EV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml");

        $sendEvent = new SendEvent($company->certificate->path, $company->certificate->password);
        $sendEvent->To = $company->software->url_event;

        if ($request->GuardarEn){
            $sendEvent->contentFile = $this->zipBase64SendEvent($company, $event->code, $sender->company->identification_number, $documentReference->getPrefixAttribute().$documentReference->getNumberAttribute(), $signEvent->sign($eventXML), $request->GuardarEn."\\EVS-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}");
            $filename = "EVS-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}";
        }
        else{
            $sendEvent->contentFile = $this->zipBase64SendEvent($company, $event->code, $sender->company->identification_number, $documentReference->getPrefixAttribute().$documentReference->getNumberAttribute(), $signEvent->sign($eventXML), StorageService::tempPath("public/{$company->identification_number}/EVS-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}"));
            $filename = "EVS-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}";
        }

        $QRStr = $this->createPDFEvent($user, $company, $typeDocument, $event, $sender, $documentReference, $typeDocumentReference, $issuerparty, $typerejection, $notes, $request, $signEvent->ConsultarCUDEEVENT());

        if ($request->GuardarEn){
            try{
                $respuestadian = $sendEvent->signToSend($request->GuardarEn."\\{$company->identification_number}\\ReqEV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml")->getResponseToObject($request->GuardarEn."\\{$company->identification_number}\\RptaEV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml");
                $r = [
                    'success' => true,
                    'message' => "{$typeDocument->name} #{$documentReference->getPrefixAttribute()}{$documentReference->getNumberAttribute()} generada con éxito",
                    'ResponseDian' => $respuestadian,
                    'cude' => $signEvent->ConsultarCUDEEVENT(),
                    'certificate_days_left' => $certificate_days_left,
                ];
                if($respuestadian->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->IsValid == 'true'){
                    if($request->event_id == "5"){
                        $invoice = Document::where('identification_number', '=', $company->identification_number)
                                            ->where('prefix', '=', $documentReference->prefix)
                                            ->where('number', '=', $documentReference->number)
                                            ->where('state_document_id', '=', 1)->get();
                        $r = array_merge($r, array('transmitter_id' => $invoice[0]->identification_number,
                                                   'transmitter_name' => $sender->name,
                                                   'receiver_id' => $invoice[0]->customer,
                                                   'receiver_name' => $customer_name,
                                                   'invoice_number' => $invoice[0]->number,
                                                   'invoice_cufe' => $invoice[0]->date_issue,
                                                   'invoice_total' => $invoice[0]->total,
                                                   'invoice_tax' => $invoice[0]->total_tax));
                    }
                    else{
                        $invoice = ReceivedDocument::where('identification_number', '=', $user->company->identification_number)
                                                    ->where('customer', '=', $sender->company->identification_number)
                                                    ->where('prefix', '=', $documentReference->prefix)
                                                    ->where('number', '=', $documentReference->number)
                                                    ->where('state_document_id', '=', 1)->get();
                        $r = array_merge($r, array('transmitter_id' => $invoice[0]->customer,
                                                   'transmitter_name' => $sender->name,
                                                   'receiver_id' => $invoice[0]->identification_number,
                                                   'receiver_name' => $invoice[0]->name_seller,
                                                   'invoice_number' => $invoice[0]->number,
                                                   'invoice_cufe' => $invoice[0]->date_issue,
                                                   'invoice_total' => $invoice[0]->total,
                                                   'invoice_tax' => $invoice[0]->total_tax));
                    }
                    if(count($invoice) > 0){
                        switch($event->id){
                            case 1:
                                $invoice[0]->acu_recibo = 1;
                                $invoice[0]->cude_acu_recibo = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_acu_recibo = json_encode($r);
                                break;
                            case 2:
                                $invoice[0]->rechazo = 1;
                                $invoice[0]->cude_rechazo = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_rechazo = json_encode($r);
                                break;
                            case 3:
                                $invoice[0]->rec_bienes = 1;
                                $invoice[0]->cude_rec_bienes = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_rec_bienes = json_encode($r);
                                break;
                            case 4:
                                $invoice[0]->aceptacion = 1;
                                $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_aceptacion = json_encode($r);
                                break;
                            case 5:
                                $invoice[0]->aceptacion = 1;
                                $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_aceptacion = json_encode($r);
                                break;
                            }
                            $invoice[0]->request_api = json_encode($request->all());
                            $invoice[0]->save();

                        if(isset($request->sendmail)){
                            if($request->sendmail){
                                if(count($invoice) > 0){
                                    try{
                                        Mail::to($user->email)->send(new EventMail($invoice, $sender, $user, $event, $request, $filename));
                                        if($request->sendmailtome)
                                            Mail::to($sender->email)->send(new EventMail($invoice, $sender, $user,  $event, $request, $filename));
                                        if($request->email_cc_list){
                                            foreach($request->email_cc_list as $email)
                                                Mail::to($email)->send(new EventMail($invoice, $sender, $user,  $event, $request, $filename));
                                        }
                                    } catch (\Exception $m) {
                                        \Log::debug($m->getMessage());
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian)),
                ];
            }
            return $r;
        }
        else{
            try{
                $respuestadian = $sendEvent->signToSend(StorageService::tempPath("public/{$company->identification_number}/ReqEV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml"))->getResponseToObject(StorageService::tempPath("public/{$company->identification_number}/RptaEV-{$event->code}-{$sender->company->identification_number}-{$documentReference->getPrefixAttribute()}-{$documentReference->getNumberAttribute()}.xml"));
                $r = [
                    'success' => true,
                    'message' => "{$typeDocument->name} #{$documentReference->getPrefixAttribute()}{$documentReference->getNumberAttribute()} generada con éxito",
                    'ResponseDian' => $respuestadian,
                    'cude' => $signEvent->ConsultarCUDEEVENT(),
                    'certificate_days_left' => $certificate_days_left,
                ];
                if($respuestadian->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->IsValid == 'true'){
                    if($request->event_id == "5"){
                        $invoice = Document::where('identification_number', '=', $company->identification_number)
                                            ->where('prefix', '=', $documentReference->prefix)
                                            ->where('number', '=', $documentReference->number)
                                            ->where('state_document_id', '=', 1)->get();
                        $r = array_merge($r, array('transmitter_id' => $invoice[0]->identification_number,
                                                   'transmitter_name' => $sender->name,
                                                   'receiver_id' => $invoice[0]->customer,
                                                   'receiver_name' => $customer_name,
                                                   'invoice_number' => $invoice[0]->number,
                                                   'invoice_date' => $invoice[0]->date_issue,
                                                   'invoice_cufe' => $invoice[0]->cufe,
                                                   'invoice_total' => $invoice[0]->total,
                                                   'invoice_tax' => $invoice[0]->total_tax));
                    }
                    else{
                        $invoice = ReceivedDocument::where('identification_number', '=', $invoice_doc->identification_number)
//                        $invoice = ReceivedDocument::where('identification_number', '=', $request->seller['identification_number'])
                                                    ->where('customer', '=', $sender->company->identification_number)
                                                    ->where('prefix', '=', $documentReference->prefix)
                                                    ->where('number', '=', $documentReference->number)
                                                    ->where('state_document_id', '=', 1)->get();
//                                                    \Log::debug($request->seller['identification_number']);
//                                                    \Log::debug($sender->company->identification_number);
//                                                    \Log::debug($documentReference->prefix);
//                                                    \Log::debug($documentReference->number);
//                                                    \Log::debug(json_encode($invoice));
//                                                    \Log::debug(json_encode(ReceivedDocument::all()));
                        $r = array_merge($r, array('transmitter_id' => $invoice[0]->customer,
                                                   'transmitter_name' => $sender->name,
                                                   'receiver_id' => $invoice[0]->identification_number,
                                                   'receiver_name' => $invoice[0]->name_seller,
                                                   'invoice_number' => $invoice[0]->number,
                                                   'invoice_date' => $invoice[0]->date_issue,
                                                   'invoice_cufe' => $invoice[0]->cufe,
                                                   'invoice_total' => $invoice[0]->total,
                                                   'invoice_tax' => $invoice[0]->total_tax));
                    }
                    if(count($invoice) > 0){
                        switch($event->id){
                            case 1:
                                $invoice[0]->acu_recibo = 1;
                                $invoice[0]->cude_acu_recibo = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_acu_recibo = json_encode($r);
                                break;
                            case 2:
                                $invoice[0]->rechazo = 1;
                                $invoice[0]->cude_rechazo = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_rechazo = json_encode($r);
                                break;
                            case 3:
                                $invoice[0]->rec_bienes = 1;
                                $invoice[0]->cude_rec_bienes = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_rec_bienes = json_encode($r);
                                break;
                            case 4:
                                $invoice[0]->aceptacion = 1;
                                $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_aceptacion = json_encode($r);
                                break;
                            case 5:
                                $invoice[0]->aceptacion = 1;
                                $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                $invoice[0]->payload_aceptacion = json_encode($r);
                                break;
                        }
                        $invoice[0]->request_api = json_encode($request->all());
                        $invoice[0]->save();

                        if(isset($request->sendmail)){
                            if($request->sendmail){
                                if(count($invoice) > 0){
                                    try{
                                        Mail::to($user->email)->send(new EventMail($invoice, $sender, $user, $event, $request, $filename));
                                        if($request->sendmailtome)
                                            Mail::to($sender->email)->send(new EventMail($invoice, $sender, $user,  $event, $request, $filename));
                                        if($request->email_cc_list){
                                            foreach($request->email_cc_list as $email)
                                                Mail::to($email)->send(new EventMail($invoice, $sender, $user,  $event, $request, $filename));
                                        }
                                    } catch (\Exception $m) {
                                        \Log::debug($m->getMessage());
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    if($respuestadian->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->ErrorMessage->string === "Regla: 90, Rechazo: Documento procesado anteriormente."){
                       if($request->event_id == "5")
                            $invoice = Document::where('identification_number', '=', $company->identification_number)
                                                ->where('prefix', '=', $documentReference->prefix)
                                                ->where('number', '=', $documentReference->number)
                                                ->where('state_document_id', '=', 1)->get();
                        else
                            $invoice = ReceivedDocument::where('identification_number', '=', $user->company->identification_number)
                                                        ->where('customer', '=', $sender->company->identification_number)
                                                        ->where('prefix', '=', $documentReference->prefix)
                                                        ->where('number', '=', $documentReference->number)
                                                        ->where('state_document_id', '=', 1)->get();
                        if(count($invoice) > 0){
                            switch($event->id){
                                case 1:
                                    $invoice[0]->acu_recibo = 1;
                                    if(is_null($invoice[0]->cude_acu_recibo) || $invoice[0]->cude_acu_recibo == ""){
                                        $invoice[0]->cude_acu_recibo = $signEvent->ConsultarCUDEEVENT();
                                        $invoice[0]->payload_acu_recibo = json_encode($r);
                                    }
                                    break;
                                case 2:
                                    $invoice[0]->rechazo = 1;
                                    if(is_null($invoice[0]->cude_rechazo) || $invoice[0]->cude_rechazo == ""){
                                        $invoice[0]->cude_rechazo = $signEvent->ConsultarCUDEEVENT();
                                        $invoice[0]->payload_rechazo = json_encode($r);
                                    }
                                    break;
                                case 3:
                                    $invoice[0]->rec_bienes = 1;
                                    if(is_null($invoice[0]->cude_rec_bienes) || $invoice[0]->cude_rec_bienes == ""){
                                        $invoice[0]->cude_rec_bienes = $signEvent->ConsultarCUDEEVENT();
                                        $invoice[0]->payload_rec_bienes = json_encode($r);
                                    }
                                    break;
                                case 4:
                                    $invoice[0]->aceptacion = 1;
                                    if(is_null($invoice[0]->cude_aceptacion) || $invoice[0]->cude_aceptacion == ""){
                                        $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                        $invoice[0]->payload_aceptacion = json_encode($r);
                                    }
                                    break;
                                case 5:
                                    $invoice[0]->aceptacion = 1;
                                    if(is_null($invoice[0]->cude_aceptacion) || $invoice[0]->cude_aceptacion == ""){
                                        $invoice[0]->cude_aceptacion = $signEvent->ConsultarCUDEEVENT();
                                        $invoice[0]->payload_aceptacion = json_encode($r);
                                    }
                                    break;
                            }
                            $invoice[0]->request_api = json_encode($request->all());
                            $invoice[0]->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage().' '.preg_replace("/[\r\n|\n|\r]+/", "", json_encode($respuestadian)),
                ];
            }
            return $r;
        }
    }
}
