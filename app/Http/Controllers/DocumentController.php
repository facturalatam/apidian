<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Document;
use App\Http\Resources\DocumentCollection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use App\Services\StorageService;

class DocumentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
       // $list =  new CompaniesCollection(User::all());
        //return json_encode($list);
        return view('documents.index') ;
    }



    public function records(Request $request)
    {
        $records = Document::all();
        return new DocumentCollection($records);
    }


    public function downloadxml($xml)
    {
        //$invoice =  Document::find($id);
        return StorageService::downloadAuto($xml);
    }

    public function downloadpdf($pdf)
    {

        return StorageService::downloadAuto($pdf);
       // return false;
        //$invoice =  Document::find($id);
       // return response()->download(storage_path("app\FES-SETP{$invoice->number}.xml"));
    }

    public function changeState(Request $request)
    {
        $document = Document::findOrFail($request->document_id);

        if($document) {
            $document->state_document_id = 1;
            $document->save();
        }

        return redirect()->back();
    }

}
