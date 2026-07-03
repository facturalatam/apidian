<?php

namespace App\Http\Controllers;

use App\Country;
use App\Language;
use App\TypeRegime;
use App\TypeDocument;
use App\TypeCurrency;
use App\Municipality;
use App\TypeLiability;
use App\TypeOperation;
use App\TypeEnvironment;
use App\TypeOrganization;
use App\TypeDocumentIdentification;
use PDF;
use QrCode;
use Storage;

class ListingController extends Controller
{
    /**
     * index.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return [
            'Country' => Country::all()->pluck('name', 'id'),
            'Language' => Language::all()->pluck('name', 'id'),
            'TypeRegime' => TypeRegime::all()->pluck('name', 'id'),
            'TypeDocument' => TypeDocument::all()->pluck('name', 'id'),
            'TypeCurrency' => TypeCurrency::all()->pluck('name', 'id'),
            'Municipality' => Municipality::all()->pluck('name', 'id'),
            'TypeLiability' => TypeLiability::all()->pluck('name', 'id'),
            'TypeOperation' => TypeOperation::all()->pluck('name', 'id'),
            'TypeEnvironment' => TypeEnvironment::all()->pluck('name', 'id'),
            'TypeOrganization' => TypeOrganization::all()->pluck('name', 'id'),
            'TypeDocumentIdentification' => TypeDocumentIdentification::all()->pluck('name', 'id'),
        ];
    }

    public function generatePDF()
    {
        define("DOMPDF_ENABLE_REMOTE", true);
        $data = ['title' => 'Welcome to HDTuto.com']; 
        $pdf = PDF::loadView('invoice', $data)->setWarnings(false);
        $filename = 'facturas/pdf/'.date("Y-m-d").'/fehaytic' . mt_rand(0,99999) .'.pdf';
        Storage::put($filename, $pdf->output());
        //return $pdf->download($filename);
        return view('invoice');
    }
}
