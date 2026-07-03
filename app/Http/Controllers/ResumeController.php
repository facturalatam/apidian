<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Company;
use App\Document;
use Illuminate\Support\Facades\DB;

class ResumeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function index()
    {
    }

    public function information_totals($nit, $desde = NULL, $hasta = NULL)
    {
        set_time_limit(0);
        ini_set("pcre.backtrack_limit", "5000000");
        if($desde && !$hasta)
          $hasta = $desde;
        else
          if(!$desde && $hasta)
            $desde = $hasta;
          else
            if(!$desde && !$hasta)
            {
                $desde = '1900-01-01';
                $hasta = '2100-01-01';
            }

        $company = Company::where('identification_number', $nit)->first();

        if(!$company)
        {
            return [
                'success' => false,
                'message' => 'No se encontraron datos del NIT',
            ];
        }

//        $i = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 1)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $i = DB::select("SELECT count(*) as total FROM documents WHERE state_document_id = 1 AND identification_number = ? AND type_document_id = ? AND date_issue >= ? AND date_issue <= ?", [$company->identification_number, 1, $desde, $hasta])[0]->total;
//        $c = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 4)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $c = DB::select("SELECT count(*) as total FROM documents WHERE state_document_id = 1 AND identification_number = ? AND type_document_id = ? AND date_issue >= ? AND date_issue <= ?", [$company->identification_number, 4, $desde, $hasta])[0]->total;
//        $d = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 5)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $d = DB::select("SELECT count(*) as total FROM documents WHERE state_document_id = 1 AND identification_number = ? AND type_document_id = ? AND date_issue >= ? AND date_issue <= ?", [$company->identification_number, 5, $desde, $hasta])[0]->total;
//        $ds = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 11)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $ds = DB::select("SELECT count(*) as total FROM documents WHERE state_document_id = 1 AND identification_number = ? AND type_document_id = ? AND date_issue >= ? AND date_issue <= ?", [$company->identification_number, 11, $desde, $hasta])[0]->total;
//        $nds = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 13)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $nds = DB::select("SELECT count(*) as total FROM documents WHERE state_document_id = 1 AND identification_number = ? AND type_document_id = ? AND date_issue >= ? AND date_issue <= ?", [$company->identification_number, 13, $desde, $hasta])[0]->total;
//        $pos = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 15)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $pos = DB::select("SELECT count(*) as total FROM documents WHERE state_document_id = 1 AND identification_number = ? AND type_document_id = ? AND date_issue >= ? AND date_issue <= ?", [$company->identification_number, 15, $desde, $hasta])[0]->total;
//        $ncp = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 26)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $ncp = DB::select("SELECT count(*) as total FROM documents WHERE state_document_id = 1 AND identification_number = ? AND type_document_id = ? AND date_issue >= ? AND date_issue <= ?", [$company->identification_number, 26, $desde, $hasta])[0]->total;
//        $ndp = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 25)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $ndp = DB::select("SELECT count(*) as total FROM documents WHERE state_document_id = 1 AND identification_number = ? AND type_document_id = ? AND date_issue >= ? AND date_issue <= ?", [$company->identification_number, 25, $desde, $hasta])[0]->total;

        $invoice = (object)[
            'name' => 'Factura de Venta Nacional',
            'count' => $i,
        ];

        $credit_note = (object)[
            'name' => 'Nota Credito',
            'count' => $c,
        ];

        $debit_note = (object)[
            'name' => 'Nota Debito',
            'count' => $d,
        ];

        $support_document = (object)[
            'name' => 'Documento Soporte a No Obligados a Facturar',
            'count' => $ds,
        ];

        $support_document_note = (object)[
            'name' => 'Notas de Ajuste al Documento Soporte a No Obligados a Facturar',
            'count' => $nds,
        ];

        $pos = (object)[
            'name' => 'Documento Equivalente POS',
            'count' => $pos,
        ];

        $pos_credit_note = (object)[
            'name' => 'Nota Credito a Documento POS',
            'count' => $ncp,
        ];

        $pos_debit_note = (object)[
            'name' => 'Nota Debito a Documento POS',
            'count' => $ndp,
        ];

        return [
            'success' => true,
            'message' => 'NIT Encontrado',
            'data'=> array($invoice, $credit_note, $debit_note, $support_document, $support_document_note, $pos, $pos_credit_note, $pos_debit_note),
            'company' => $company->user->name
        ];
    }

    public function information($nit, $desde = NULL, $hasta = NULL)
    {
        if($desde && !$hasta)
          $hasta = $desde;
        else
          if(!$desde && $hasta)
            $desde = $hasta;
          else
            if(!$desde && !$hasta)
            {
                $desde = '1900-01-01';
                $hasta = '2100-01-01';
            }

        $company = Company::where('identification_number', $nit)->first();

        if(!$company)
        {
            return [
                'success' => false,
                'message' => 'No se encontraron datos del NIT',
            ];
        }

        $i = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 1)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $c = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 4)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $d = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 5)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();
        $p = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 15)->whereDate('date_issue', '>=', $desde)->whereDate('date_issue', '<=', $hasta)->get();

        $invoice = (object)[
            'type_document_id' => 1,
            'name' => 'Factura de Venta Nacional',
            'count' => count($i),
            'documents' => $i
        ];

        $credit_note = (object)[
            'type_document_id' => 4,
            'name' => 'Nota Credito',
            'count' => count($c),
            'documents' => $c
        ];

        $debit_note = (object)[
            'type_document_id' => 5,
            'name' => 'Nota Debito',
            'count' => count($d),
            'documents' => $d
        ];

        $pos = (object)[
            'type_document_id' => 15,
            'name' => 'Documento Equivalente POS',
            'count' => count($p),
            'documents' => $p
        ];

        return [
            'success' => true,
            'message' => 'NIT Encontrado',
            'data'=> array($invoice, $credit_note, $debit_note, $pos),
            'company' => $company->user->name
        ];
    }

    public function information_by_page($nit, $page)
    {
        if($page <= 0)
            $page = 1;

        $company = Company::where('identification_number', $nit)->first();

        if(!$company)
        {
            return [
                'success' => false,
                'message' => 'No se encontraron datos del NIT',
            ];
        }

        $perPage = 100;

        $i = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 1)->skip(($page - 1) * $perPage)->take($perPage)->oldest()->get();
        $c = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 4)->skip(($page - 1) * $perPage)->take($perPage)->oldest()->get();
        $d = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 5)->skip(($page - 1) * $perPage)->take($perPage)->oldest()->get();
        $p = Document::where('state_document_id', 1)->where('identification_number', $company->identification_number)->where('type_document_id', 15)->skip(($page - 1) * $perPage)->take($perPage)->oldest()->get();

        $invoice = (object)[
            'name' => 'Factura de Venta Nacional',
            'count' => count($i),
            'documents' => $i
        ];

        $credit_note = (object)[
            'name' => 'Nota Credito',
            'count' => count($c),
            'documents' => $c
        ];

        $debit_note = (object)[
            'name' => 'Nota Debito',
            'count' => count($d),
            'documents' => $d
        ];

        $pos = (object)[
            'name' => 'Documento Equivalente POS',
            'count' => count($p),
            'documents' => $p
        ];

        return [
            'success' => true,
            'message' => 'NIT Encontrado',
            'data'=> array($invoice, $credit_note, $debit_note, $pos),
            'company' => $company->user->name
        ];
    }

}