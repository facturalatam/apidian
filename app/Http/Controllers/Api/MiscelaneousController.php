<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Company;
use App\Resolution;
use App\Document;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\JoinPDFsRequest;
use App\Http\Requests\Api\NextConsecutiveRequest;
use App\Services\StorageService;
use App\Traits\DocumentTrait;
use Exception;
use PDFMerger;
use Goutte\Client as ClientScrap;
use Symfony\Component\HttpClient\HttpClient;

class MiscelaneousController extends Controller
{
    use DocumentTrait;
    protected $nameclient;

    public function NextConsecutive(NextConsecutiveRequest $request)
    {

        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        $resolution = $company->resolutions->where('type_document_id', $request->type_document_id)->where('prefix', $request->prefix)->first();

        try{
            $document = Document::where('identification_number', $company->identification_number)
                ->where('type_document_id', $request->type_document_id)
                ->where('state_document_id', 1)
                ->where('prefix', $resolution->prefix)
                ->orderByRaw('CAST(number AS UNSIGNED) DESC')
                ->first();
            return [
                'success' => true,
                'type_document_id' => $request->type_document_id,
                'prefix' => $resolution->prefix,
                'number' => ($document) ? ((int)$document->number + 1) : (int)$resolution->from,
            ];
        }
        catch(Exception $e) {
            return [
                'success' => false,
                'message' => "{$e->getLine()} - {$e->getMessage()}"
            ];
        }

    }

    public function joinPDFs(JoinPDFsRequest $request)
    {
        try {
            $user = auth()->user();

            $company = $user->company;

            $new_pdf = new PDFMerger();

            foreach($request->pdfs as $pdf){
                if($pdf['type_document_id'] == 1 || $pdf['type_document_id'] == 2 || $pdf['type_document_id'] == 3 || $pdf['type_document_id'] == 12)
                  $type_document = "FES-";
                else
                    if($pdf['type_document_id'] == 4)
                      $type_document = "NCS-";
                    else
                        if($pdf['type_document_id'] == 5)
                          $type_document = "NDS-";
                        else
                            if($pdf['type_document_id'] == 11)
                              $type_document = "DSS-";
                $pdfPath = StorageService::localPath("public/{$company->identification_number}/{$type_document}{$pdf['prefix']}{$pdf['number']}".".pdf");
                $new_pdf->addPDF($pdfPath, 'all');
            }

            $outputRelative = "public/{$company->identification_number}/{$request->name_joined_pdfs}";
            $outputPath = StorageService::tempPath($outputRelative);
            StorageService::ensureDirectory("public/{$company->identification_number}");
            $new_pdf->merge('file', $outputPath);
            StorageService::uploadIfS3($outputRelative, $outputPath);
            return [
                'success' => true,
                'message' => 'Operacion realizada con exito.',
                'pdfbase64' => base64_encode(file_get_contents($outputPath))
            ];
        }
        catch(Exception $e) {
            return [
                'success' => false,
                'message' => "{$e->getLine()} - {$e->getMessage()}"
            ];
        }
    }

    public function setNameClient($name)
    {
        $this->nameclient = $name;
    }

    public function SearchCompany($nit)
    {
        $client = new ClientScrap(HttpClient::create(['timeout' => 80, 'verify_peer' => false]));
        $crawler = $client->request('GET', "https://www.einforma.co/servlet/app/portal/ENTP/prod/LISTA_EMPRESAS/razonsocial/{$nit}");
        $name = $crawler->filter('#titEtiqueta')->text("No se encontro NIT.");
        if($name !== "No se encontro NIT."){
            $nodeValues = $crawler->filter('td')->each(function ($node, $i) {
                return $node->text();
            });
            return [
                'success' => "true",
                'name' => $nodeValues[6],
                'type' => $nodeValues[8],
                'direction' =>  $nodeValues[12],
                'state' =>  $nodeValues[10],
                'activity' =>  $nodeValues[18],
            ];
        }else{
            return [
                'success' => "false",
                'message' => "No se encontro el NIT",
            ];
        }
    }

    public function nameByNit($nit)
    {
       $client = new ClientScrap(HttpClient::create(['timeout' => 80, 'verify_peer' => false]));
       $crawler = $client->request('GET', "https://www.einforma.co/servlet/app/portal/ENTP/prod/LISTA_EMPRESAS/razonsocial/{$nit}");
       $node = $crawler->filter('h1#titEtiqueta');
        if ($node->count() > 0) {
            $name = $node->getNode(0)->childNodes->item(0)->nodeValue;
            $this->setNameClient(trim($name));
        }
       if(!is_null($this->nameclient)){
           $arrayName = explode(" ", $this->nameclient);
           if(count($arrayName) == 1)
               return [
                   'success' => true,
                   'result' => [
                                   'primer_nombre' => $arrayName[0],
                                   'otros_nombres' => '',
                                   'primer_apellido' => '',
                                   'segundo_apellido' => ''
                               ]
                    ];
           else
               if(count($arrayName) == 2)
                   return [
                       'success' => true,
                       'result' => [
                                       'primer_nombre' => $arrayName[1],
                                       'otros_nombres' => '',
                                       'primer_apellido' => $arrayName[0],
                                       'segundo_apellido' => ''
                                   ]
                        ];
               else
                   if(count($arrayName) == 3)
                        return [
                            'success' => true,
                            'result' => [
                                            'primer_nombre' => $arrayName[2],
                                            'otros_nombres' => '',
                                            'primer_apellido' => $arrayName[0],
                                            'segundo_apellido' => $arrayName[1]
                                        ]
                            ];
                    else
                        if(count($arrayName) == 4)
                            return [
                                'success' => true,
                                'result' => [
                                                'primer_nombre' => $arrayName[2],
                                                'otros_nombres' => $arrayName[3],
                                                'primer_apellido' => $arrayName[0],
                                                'segundo_apellido' => $arrayName[1]
                                            ]
                            ];
       }
       else
          return [
                'success' => false,
                'message' => "No se encontro el NIT."
          ];
   }

}
