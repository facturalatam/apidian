<?php

namespace App\Http\Controllers;

use App\Company;
use App\Customer;
use App\Document;
use App\User;
use App\Mail\InvoiceMail;
use App\Mail\PasswordCustomerMail;
use App\Mail\RetrievePasswordCustomerMail;
use App\Mail\RetrievePasswordSellerMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\DocumentTrait;
use DB;

class OwnerApiLoginController extends Controller
{
    use DocumentTrait;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function OwnerPassword(Request $request)
    {
        return view('resetownerpassword', compact('request'));
    }

    protected function ResetOwnerPassword(Request $request)
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

        $fp = fopen(storage_path("filepassowner.api"), "w");
        fwrite($fp, $request->password);
        fclose($fp);
        return view('customerloginmensaje', ['titulo' => 'Actualizacion de password', 'mensaje' => 'El password ha sido actualizado satisfactoriamente.']);
    }

    /**
     * Show the seller login form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function ShowOwnerLoginForm()
    {
        return view('ownerapilogin');
    }

    protected function OwnerSearch(Request $request)
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
            $field =  "identification_number";
            break;
          case "8":
            $field =  "customer";
            break;
          case "9":
            $field =  "prefix";
            break;
        }
        if($request->searchfield == "Seleccione campo para filtrar.")
            $documents = Document::where('state_document_id', 1)->paginate(20);
        else
            if($field == 'type_document_id')
                $documents = Document::where($field, '=', $type_document_id)->where('number', 'like', '%'.$number.'%')->where('state_document_id', 1)->paginate(20);
            else
                $documents = Document::where($field, 'like', '%'.$request->searchvalue.'%')->where('state_document_id', 1)->paginate(20);
//        $documents = $documents->load('type_document');
        return view('homeowner', compact('documents'));
    }

    protected function PasswordOwnerVerify(Request $request)
    {
        $rules = [
            'password' => [
                'required',
                'min:5',
                'passwordowner_verify:'.$request->password,
            ],
        ];
        if($request->verificar <> "FALSE")
            $this->validate($request, $rules);
        $documents = Document::where('state_document_id', 1)->paginate(20);
//        $documents = $documents->load('type_document');
        return view('homeowner', compact('documents'));
    }
}
