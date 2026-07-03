<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Company;
use App\Customer;
use App\Document;
use App\ReceivedDocument;
use Illuminate\Support\Str;
use App\User;
use App\Mail\InvoiceMail;
use App\Mail\PasswordCustomerMail;
use App\Mail\RetrievePasswordCustomerMail;
use App\Mail\RetrievePasswordSellerMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\DocumentTrait;
use App\Services\StorageService;

class SellerLoginController extends Controller
{
    use DocumentTrait;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        
    }

    /**
     * Show the seller login form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function ShowSellerLoginForm($company_idnumber)
    {
        $company = Company::where('identification_number', '=', $company_idnumber)->get();
        if (count($company) > 0)
            if($company[0]->allow_seller_login)
                return view('sellerlogin', compact('company_idnumber'));
            else
                return view('customerloginmensaje', ['titulo' => 'Error en el ingreso para empresas', 'mensaje' => 'Esta empresa no tiene habilitada esta opcion']);
        else
            return view('customerloginmensaje', ['titulo' => 'Error en el ingreso para empresas', 'mensaje' => 'Los datos de emisor no se encuentran registrados.']);
    }

    protected function SellerPassword(Request $request, $company_idnumber)
    {
        return view('resetsellerpassword', compact('request', 'company_idnumber'));
    }

    protected function SellersDocumentsReceptionView(Request $request, $company_idnumber)
    {
        return view('sellersdocumetsreception', compact('request', 'company_idnumber'));
    }

    protected function cleantmp_route($tmp_route, $company_idnumber){
        StorageService::ensureDirectory('received/'.$company_idnumber);

        $files = glob(StorageService::tempPath($tmp_route)."/*.*");
        foreach($files as $file){
            if(is_file($file))
                if(!strpos($file, '.xml'))
                    unlink($file);
                else{
                    $relativePath = 'received/'.$company_idnumber.'/'.basename($file);
                    StorageService::putLocalFile($relativePath, $file);
                    unlink($file);
                }
        }
        rmdir(StorageService::tempPath($tmp_route));
    }

    protected function SellersDocumentsReception(Request $request, $company_idnumber)
    {
        $tmp_route = Str::random(15);
        try{
            $localTmpPath = StorageService::tempPath($tmp_route);
            if (!is_dir($localTmpPath)){
                $old = umask(0);
                mkdir($localTmpPath, 0777);
                umask($old);
            }

            $uploadedFileName = basename($_FILES['formFileInput']['name']);
            $localFilePath = $localTmpPath."/".$uploadedFileName;
            if(move_uploaded_file($_FILES['formFileInput']['tmp_name'], $localFilePath)){
                if(strpos($uploadedFileName, '.pdf')){
                    $exists = ReceivedDocument::where('customer', $company_idnumber)->where('pdf', $uploadedFileName)->get();
                    if(count($exists) > 0){
                        $relativePath = 'received/'.$company_idnumber.'/'.$uploadedFileName;
                        StorageService::putLocalFile($relativePath, $localFilePath);
                        unlink($localFilePath);
                        $this->cleantmp_route($tmp_route, $company_idnumber);
                        return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".$uploadedFileName." fue cargado exitosamente"]);
                    }
                    else{
                        $this->cleantmp_route($tmp_route, $company_idnumber);
                        return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".$uploadedFileName." no corresponde a ningun registro de la base de datos"]);
                    }
                }

                $att = new \DOMDocument('1.0', 'utf-8');
                $att->preserveWhiteSpace = false;
                $att->formatOutput = true;

                $attXMLStr = base64_encode($this->file_get_contents_utf8($localFilePath));

                if(!$att->loadXML(base64_decode($attXMLStr))){
                    $this->cleantmp_route($tmp_route, $company_idnumber);
                    return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".basename($_FILES['formFileInput']['name'])." no se pudo cargar, revise los problemas asociados"]);
                }
                else{
                    if(!strpos($att->saveXML(), "<AttachedDocument")){
                        $this->cleantmp_route($tmp_route, $company_idnumber);
                        return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".basename($_FILES['formFileInput']['name'])." no es un AttachedDocument XML"]);
                    }
                    if(!strpos($att->saveXML(), "<ApplicationResponse")){
                        $this->cleantmp_route($tmp_route, $company_idnumber);
                        return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".basename($_FILES['formFileInput']['name'])." no se encontro el ApplicationResponse dentro del AttachedDocument XML"]);
                    }
                    if(!strpos($att->saveXML(), "<Invoice")){
                        $this->cleantmp_route($tmp_route, $company_idnumber);
                        return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".basename($_FILES['formFileInput']['name'])." no corresponde al AttachedDocument XML de un documento Invoice"]);
                    }
                    $invoiceXMLStr = $att->documentElement->getElementsByTagName('Description')->item(0)->nodeValue;
                    $invoiceXMLStr = substr(base64_decode($attXMLStr), strpos(base64_decode($attXMLStr), "<Invoice"), strpos(base64_decode($attXMLStr), "/Invoice>") - strpos(base64_decode($attXMLStr), "<Invoice") + 9);
                    $invoiceXMLStr = preg_replace("/[\r\n|\n|\r]+/", "","<?xml version=\"1.0\" encoding=\"utf-8\"?>".$invoiceXMLStr);

                    $invoice_doc = new ReceivedDocument();
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

                    $invoice_doc->xml = basename($_FILES['formFileInput']['name']);
                    $invoice_doc->cufe = $this->getTag($invoiceXMLStr, 'UUID', 0)->nodeValue;
                    $invoice_doc->date_issue = $this->getTag($invoiceXMLStr, 'IssueDate', 0)->nodeValue.' '.str_replace('-05:00', '', $this->getTag($invoiceXMLStr, 'IssueTime', 0)->nodeValue);
                    $invoice_doc->sale = $this->getTag($invoiceXMLStr, 'TaxInclusiveAmount', 0)->nodeValue;
                    if(isset($this->getTag($invoiceXMLStr, 'AllowanceTotalAmount', 0)->nodeValue))
                        $invoice_doc->total_discount = $this->getQuery($invoiceXMLStr, "cac:LegalMonetaryTotal/cbc:AllowanceTotalAmount")->nodeValue;
//                        $invoice_doc->total_discount =  $this->getTag($invoiceXMLStr, 'AllowanceTotalAmount', 0)->nodeValue;
                    else
                        $invoice_doc->total_discount = 0;
                    $invoice_doc->subtotal = $this->getTag($invoiceXMLStr, 'LineExtensionAmount', 0)->nodeValue;
                    $invoice_doc->total_tax = $invoice_doc->sale - $invoice_doc->subtotal;
                    $invoice_doc->total = $this->getQuery($invoiceXMLStr, "cac:LegalMonetaryTotal/cbc:PayableAmount")->nodeValue;
//                    $invoice_doc->total = $this->getTag($invoiceXMLStr, 'PayableAmount', 0)->nodeValue;
                    $invoice_doc->ambient_id = $this->getTag($invoiceXMLStr, 'ProfileExecutionID', 0)->nodeValue;
                    $invoice_doc->pdf = str_replace('.xml', '.pdf', basename($_FILES['formFileInput']['name']));
                    $invoice_doc->acu_recibo = 0;
                    $invoice_doc->rec_bienes = 0;
                    $invoice_doc->aceptacion = 0;
                    $invoice_doc->rechazo = 0;
                    if($invoice_doc->customer != $company_idnumber){
                        $this->cleantmp_route($tmp_route, $company_idnumber);
                        return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".basename($_FILES['formFileInput']['name'])." no corresponde un AttachedDocument XML del adquiriente ".$company_idnumber]);
                    }
                    $exists = ReceivedDocument::where('customer', $company_idnumber)->where('identification_number', $invoice_doc->identification_number)->where('prefix', $invoice_doc->prefix)->where('number', $invoice_doc->number)->get();
                    if(count($exists) == 0)
                        $invoice_doc->save();
                    else{
                        $this->cleantmp_route($tmp_route, $company_idnumber);
                        return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".basename($_FILES['formFileInput']['name'])." ya existe en la base de datos..."]);
                    }
                    $this->cleantmp_route($tmp_route, $company_idnumber);
                    return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".basename($_FILES['formFileInput']['name'])." fue cargado satisfactoriamente..."]);
                }
                $this->cleantmp_route($tmp_route, $company_idnumber);
            }
            else
                return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".basename($_FILES['formFileInput']['name'])." no se pudo cargar, revise los problemas asociados"]);
        } catch (\Exception $e) {
            $this->cleantmp_route($tmp_route, $company_idnumber);
            return view('customerloginmensaje', ['titulo' => 'Recepcion de documentos...', 'mensaje' => "El archivo ".basename($_FILES['formFileInput']['name'])." no se pudo cargar... ".$e->getMessage()]);
        }
    }

    protected function SellersRadianEventsView(Request $request, $company_idnumber){
        $documents = ReceivedDocument::where('customer','=',$company_idnumber)->where('state_document_id', '=', 1)->paginate(10);
//        $documents = $documents->load('type_document');
//        $documents = Document::with('type_document')->get();
//        return view('homecustomers')->with('documents', $documents);
        return view('homeradianevents', compact('documents', 'company_idnumber'));
    }

    protected function ResetSellerPassword(Request $request, $company_idnumber)
    {
        $rules = [
            'password' => [
                'required',
                'min:5',
            ],
            'password_confirmation' => [
                'required',
                'min:5',
                'igual_a:'.$request->password,
            ],
        ];
        $this->validate($request, $rules);

        $seller = Company::where('identification_number', '=', $company_idnumber)->get()->first();
        $seller->password = bcrypt($request->password);
        $seller->save();
        $user = User::where('id', $seller->user_id)->get()->first();
        Mail::to($user->email)->send(new PasswordCustomerMail($seller, $request->password));
        return view('customerloginmensaje', ['titulo' => 'Actualizacion de password', 'mensaje' => 'El password ha sido actualizado satisfactoriamente.']);
    }

    protected function RetrievePasswordSeller($company_idnumber, $mostrarvista = 'YES')
    {
        $seller = Company::where('identification_number', '=', $company_idnumber)->get()->first();
        if (!$seller) {
            if ($mostrarvista == 'YES') {
                return view('customerloginmensaje', ['titulo' => 'Solicitud de nuevo password', 'mensaje' => 'No se encontró la empresa.']);
            }

            return [
                'success' => false,
                'message' => 'No se encontró la empresa.',
            ];
        }

        $password = Str::random(6);
        $seller->newpassword = bcrypt($password);
        $seller->save();
        $user = User::where('id', $seller->user_id)->get()->first();
        if (!$user || empty($user->email)) {
            $seller->newpassword = null;
            $seller->save();
            if ($mostrarvista == 'YES') {
                return view('customerloginmensaje', ['titulo' => 'Solicitud de nuevo password', 'mensaje' => 'No hay email registrado para enviar la recuperación.']);
            }

            return [
                'success' => false,
                'message' => 'No hay email registrado para enviar la recuperación.',
            ];
        }

        try {
            Mail::to($user->email)->send(new RetrievePasswordSellerMail($seller, $password));
        } catch (\Throwable $e) {
            Log::error('Error enviando RetrievePasswordSellerMail.', [
                'company_idnumber' => $company_idnumber,
                'email' => $user->email,
                'exception' => $e,
            ]);

            $seller->newpassword = null;
            $seller->save();

            if ($mostrarvista == 'YES') {
                return view('customerloginmensaje', ['titulo' => 'Solicitud de nuevo password', 'mensaje' => 'No fue posible enviar el correo. Revise la configuración SMTP (MAIL_DRIVER/MAIL_HOST/MAIL_PORT/MAIL_USERNAME/MAIL_PASSWORD) y la conectividad.']);
            }

            return [
                'success' => false,
                'message' => 'No fue posible enviar el correo. Revise configuración SMTP y conectividad.',
            ];
        }
        if($mostrarvista == 'YES')
            return view('customerloginmensaje', ['titulo' => 'Solicitud de nuevo password', 'mensaje' => 'Se ha enviado un mensaje de correo electronico a la direccion '.$user->email.', debe confirmar este mensaje para que el cambio de contraseña se haga efectivo.']);
        else
            return [
                'success' => true,
                'password' => $password,
        ];
    }

    protected function AcceptRetrievePasswordSeller($company_idnumber, $hash)
    {
        $seller = Company::where('identification_number', '=', $company_idnumber)->get()->first();
//        \Log::debug("1....  ".$hash);
        if(substr($hash, strlen($hash) - 1) == "\"")
            $hash = str_replace("\"", "", $hash);
//        \Log::debug("2....  ".$hash);
        if(strpos($hash, "(slash)") != false)
            $hash = str_replace('(slash)', '/', $hash);
//        $hash = str_replace('(slash)', '/', substr($hash, 0, strlen($hash) - 1));
//        \Log::debug("3....  ".$hash);
//        \Log::debug("4....  ".$seller->newpassword." seller->newpassword");
        if($hash == $seller->newpassword)
        {
//            \Log::debug("5....  ".$hash);
            $seller->password = $seller->newpassword;
            $seller->newpassword = NULL;
            $seller->save();
            return view('customerloginmensaje', ['titulo' => 'Confirmacion de nuevo password', 'mensaje' => 'Se ha actualizado satisfactoriamente su password de ingreso a la plataforma.']);
        }
        return view('customerloginmensaje', ['titulo' => 'Confirmacion de nuevo password', 'mensaje' => 'No ha sido posible realizar la operacion, consulte con su administrador de plataforma.']);
    }

    protected function PasswordSellerVerify(Request $request, $company_idnumber)
    {
//        $rules = [
//            'password' => 'required|min:5',
//        ];

//        Validator::make($request, [
//            'password' => [
//                'required',
//                'min:5',
//                Rule::exists('customers, password')->where(function ($query) {
//                    $query->where('identification_number', $customer_idnumber);
//                }),
//            ],
//        ]);

//        $customer = Customer::where('identification_number', '=', $customer_idnumber)->get();
//        if(count($customer) > 0)
//            if (password_verify($request->password, $customer[0]->password))
//                return "Password Valido";
//            else
//                return "Password No Valido";

        $rules = [
            'password' => [
                'required',
                'min:5',
                'passwordseller_verify:'.$company_idnumber,
//                            Rule::exists('customers', 'password')
//                                ->where(function ($query) use ($customer_idnumber) {
//                                            $query->where('identification_number', $customer_idnumber);
//                                        }),
            ],
        ];
        if($request->verificar <> "FALSE")
            $this->validate($request, $rules);
        $documents = Document::where('identification_number','=',$company_idnumber)->where('state_document_id', '=', 1)->paginate(15);
//        $documents = $documents->load('type_document');
//        $documents = Document::with('type_document')->get();
//        return view('homecustomers')->with('documents', $documents);
        return view('homesellers', compact('documents', 'company_idnumber'));
    }

    protected function SellersSearch(Request $request, $company_idnumber)
    {
        $field = "";
        switch ($request->searchfield){
          case "1":
            $field =  "type_document_id";
            $type_document_id = $request->searchfield;
            $number = $request->searchvalue;
            break;
          case "2":
            $field =  "type_document_id";
            $type_document_id = $request->searchfield;
            $number = $request->searchvalue;
            break;
          case "3":
            $field =  "type_document_id";
            $type_document_id = $request->searchfield;
            $number = $request->searchvalue;
            break;
          case "4":
            $field =  "type_document_id";
            $type_document_id = $request->searchfield;
            $number = $request->searchvalue;
            break;
          case "5":
            $field =  "type_document_id";
            $type_document_id = $request->searchfield;
            $number = $request->searchvalue;
            break;
          case "11":
            $field =  "type_document_id";
            $type_document_id = $request->searchfield;
            $number = $request->searchvalue;
            break;
          case "6":
            $field =  "date_issue";
            break;
          case "7":
            $field =  "customer";
            break;
          case "8":
            $field =  "prefix";
            break;
        }
        if($request->searchfield == "Seleccione campo para filtrar.")
            $documents = Document::where('identification_number','=',$company_idnumber)->where('state_document_id', 1)->paginate(15);
        else
            if($field == 'type_document_id')
                $documents = Document::where('identification_number','=',$company_idnumber)->where($field, '=', $type_document_id)->where('number', 'like', '%'.$number.'%')->where('state_document_id', 1)->paginate(15);
            else
                $documents = Document::where('identification_number','=',$company_idnumber)->where($field, 'like', '%'.$request->searchvalue.'%')->where('state_document_id', 1)->paginate(15);
//        $documents = $documents->load('type_document');
        return view('homesellers', compact('documents', 'company_idnumber'));
    }

    protected function SellersRadianSearch(Request $request, $company_idnumber)
    {
        $field = "";
        switch ($request->searchfield){
          case "1":
            $field = "type_document_id";
            $type_document_id = $request->searchfield;
            $number = $request->searchvalue;
            break;
          case "2":
            $field =  "identification_number";
            break;
          case "3":
            $field =  "name_seller";
            break;
          case "4":
            $field =  "acu_recibo";
            break;
          case "5":
            $field =  "rec_bienes";
            break;
          case "6":
            $field =  "aceptacion";
            break;
          case "7":
            $field =  "rechazo";
            break;
          case "8":
            $field =  "prefix";
            break;
        }
        if($request->searchfield == "Seleccione campo para filtrar.")
            $documents = ReceivedDocument::where('customer','=',$company_idnumber)->where('state_document_id', 1)->paginate(10);
        else
            if($field == 'type_document_id')
                $documents = ReceivedDocument::where('customer','=',$company_idnumber)->where($field, '=', $type_document_id)->where('number', 'like', '%'.$number.'%')->where('state_document_id', 1)->paginate(10);
            else
                if($field == "prefix" || $field == "identification_number" || $field == "name_seller")
                    $documents = ReceivedDocument::where('customer','=',$company_idnumber)->where($field, 'like', '%'.$request->searchvalue.'%')->where('state_document_id', 1)->paginate(10);
                else
                    $documents = ReceivedDocument::where('customer','=',$company_idnumber)->where($field, '=', 1)->where('state_document_id', 1)->paginate(10);
//        $documents = $documents->load('type_document');
        return view('homeradianevents', compact('documents', 'company_idnumber'));
    }
}
