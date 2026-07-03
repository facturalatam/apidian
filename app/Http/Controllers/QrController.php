<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QrController extends Controller
{
    protected function generateQr(){
        $qrData = array("NumFac"=>"A02F-00117836", "FecFac"=>"20140319105605", "NitFac"=>"808183133", "DocAdq"=>"8081972684",
                         "ValFac"=>"1000.00","ValIva"=>"160.00", "ValOtroIm"=>"0.00", "ValFacIm"=>"1160.00", "CUFE"=>"2836a15058e90baabbf6bf2e97f05564ea0324a6");
        $qrString = str_replace('=', ':', http_build_query($qrData, null, ' '));
        return \QrCode::format('svg')->size(250)
        ->generate($qrString);
    }
}
