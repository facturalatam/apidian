<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TypeDocumentIdentification;
use App\TypeDocument;
use App\TypeOrganization;
use App\TypeRegime;
use App\Country;
use App\Department;
use App\Municipality;
use App\User;
use App\TypeLiability;
use App\Http\Resources\CompaniesCollection;
use Illuminate\Support\Facades\Log;
use App\Services\PdfTextExtractor;


class ConfigurationController extends Controller
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
        return view('configurations.index') ;
    }

    public function configuration_admin()
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'isPlatformAdmin') && !$user->isPlatformAdmin()) {
            abort(403, 'No tienes permiso para crear empresas.');
        }
        return view('configurations.formadmin');
    }

    public function records(Request $request)
    {
        $records = User::all();
        return new CompaniesCollection($records);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function tables()
    {
        $type_document_identification = TypeDocumentIdentification::all();
        $type_organization = TypeOrganization::all();
        $type_regime = TypeRegime::all();
        $department = Department::where('country_id', 46)->get();
        $ids_department = $department->pluck('id');
        $municipality = Municipality::whereIn('department_id', $ids_department)->get();
        $type_document = TypeDocument::all();
        $type_liability = TypeLiability::all();


        return compact( 'type_document_identification','type_organization', 'type_regime', 'department', 'municipality', 'type_document', 'type_liability');
    }

    public function extractRut(Request $request)
    {
        if (!$request->hasFile('rut')) {
            return response()->json([
                'success' => false,
                'message' => 'No se envió el archivo RUT'
            ], 400);
        }

        $file = $request->file('rut');

        if (strtolower($file->getClientOriginalExtension()) !== 'pdf') {
            return response()->json([
                'success' => false,
                'message' => 'El archivo debe ser un PDF válido.'
            ], 400);
        }

        // Guardar el archivo temporalmente
        $destination = storage_path('app/rut_temp');
        if (!is_dir($destination)) mkdir($destination, 0777, true);

        $filename = uniqid().'.pdf';
        $file->move($destination, $filename);
        $fullPath = $destination.'/'.$filename;

        $pdftotext = config('system_configuration.pdftotext_path');
        if (!$pdftotext || !file_exists($pdftotext)) {
            return response()->json([
                'success' => false,
                'message' => 'Poppler (pdftotext) no está configurado. 
                            Añade la ruta en .env como PDFTOTEXT_PATH='
            ], 500);
        }

        // Opciones ANTES del archivo (compatibilidad Xpdf) y -enc UTF-8 para que la
        // salida no llegue en Latin-1 y los regex de extracción encuentren los campos.
        $cmd = "\"$pdftotext\" -layout -enc UTF-8 \"$fullPath\" -";
        $text = shell_exec($cmd);

        if (!$text || trim($text) == "") {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo extraer el texto del PDF (pdftotext falló)'
            ], 400);
        }

        $fields = [];

        // Cortar el texto ANTES del bloque IDENTIFICACIÓN
        $beforeIdent = preg_split('/IDENTIFICACI[ÓO]N/i', $text)[0];

        // Nit + DV
        if (preg_match('/N[uú]mero de Identificaci[oó]n Tributaria.*?\n([0-9\s\-\–]+)/iu', $beforeIdent, $m)) {

            $line = trim($m[1]);
            $clean = preg_replace('/\s+/', '', $line);

            if (preg_match('/^(\d+)\-(\d)$/', $clean, $mm)) {
                $fields['nit'] = $mm[1];
                $fields['dv']  = $mm[2];
            }
            else if (preg_match('/^(\d{9,12})(\d)$/', $clean, $mm)) {
                $fields['nit'] = $mm[1];
                $fields['dv']  = $mm[2];
            }
            else {
                Log::info("No se pudo extraer NIT y DV correctamente de: ".$clean);
            }
        }

        // Razón Social
        if (preg_match('/Raz[oó]n\s+social[:\s]*\n?(.+)/iu', $text, $m)) {
            $fields['business_name'] = trim($m[1]);
            Log::info("RAZON SOCIAL DETECTADA: ".$fields['business_name']);
        } else {
            Log::info("NO SE DETECTÓ RAZON SOCIAL");
        }

        // Dirección
        if (preg_match('/Direcci[oó]n principal\s*\n(.+)/iu', $text, $m)) {
            $fields['address'] = trim($m[1]);
        }

        // Email
        if (preg_match(
                '/Correo electr[oó]nico.*?([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,})/iu',
                $text,
                $mEmail
            )) {

            $fields['email'] = trim($mEmail[1]);
            Log::info("EMAIL DETECTADO (campo): ".$fields['email']);
        }

        // 2) Fallback: si no lo encontró, buscar cualquier correo en el PDF
        if (empty($fields['email'])) {

            if (preg_match(
                    '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,}/u',
                    $text,
                    $fallback
                )) {

                $fields['email'] = trim($fallback[0]);
                Log::info("EMAIL DETECTADO (fallback): ".$fields['email']);
            } else {
                Log::info("NO SE DETECTÓ NINGÚN EMAIL EN EL PDF");
            }
        }

        // Teléfono
        $telefonoBlock = null;
        if (preg_match('/UBICACI[ÓO]N(.*?)CLASIFICACI[ÓO]N/siu', $text, $tb)) {
            $telefonoBlock = $tb[1];
        }
        $phone = null;
        $phone2 = null;
        if ($telefonoBlock) {
            if (preg_match('/Tel[eé]fono\s*1[:\s]*([0-9 ]{8,20})/iu', $telefonoBlock, $m1)
                || preg_match('/Tel[eé]fono\s*1[:\s]*\n\s*([0-9 ]{8,20})/iu', $telefonoBlock, $m1)) {

                $clean = preg_replace('/\D/', '', $m1[1]);
                if (strlen($clean) >= 7) {   // ⬅️ mínimo 7 dígitos
                    $phone = $clean;
                }
            }

            if (preg_match('/Tel[eé]fono\s*2[:\s]*([0-9 ]{8,20})/iu', $telefonoBlock, $m2)
                || preg_match('/Tel[eé]fono\s*2[:\s]*\n\s*([0-9 ]{8,20})/iu', $telefonoBlock, $m2)) {

                $clean2 = preg_replace('/\D/', '', $m2[1]);
                if (strlen($clean2) >= 7) {   // ⬅️ mínimo 7 dígitos
                    $phone2 = $clean2;
                }
            }
        }

        if (!empty($phone)) {
            $fields['phone'] = $phone;
        } elseif (!empty($phone2)) {
            $fields['phone'] = $phone2;
        } else {
            Log::info("NO SE DETECTÓ TELÉFONO");
        }

        $ubicacionBlock = null;
        if (preg_match('/UBICACI[ÓO]N(.{0,500})/siu', $text, $block)) {
            $ubicacionBlock = $block[1];
        }

        if ($ubicacionBlock) {

            $normalized = preg_replace('/(?<=\d)\s+(?=\d)/', '', $ubicacionBlock);

            // 2) REGEX sobre texto normalizado
            if (preg_match(
                '/\b(\d{2,3})\b\s+([A-Za-zÁÉÍÓÚÑáéíóúñ]+)\s+(\d{2})\s+([A-Za-zÁÉÍÓÚÑáéíóúñ]+)\s+(\d{3})/u',
                $normalized,
                $m
            )) {

                $depCode = $m[1];    // 169
                $depName = $m[2];    // Antioquia
                $munCode = $m[3];    // 05
                $munName = $m[4];    // Medellín
                $munCode3 = $m[5];   // 001

                // Realmente el departamento es 05 (el "169" es el código de país DIAN)
                $fields['department_code'] = $munCode;
                $fields['department_text'] = ucfirst(strtolower($depName));

                $fields['municipality_code'] = $munCode . $munCode3; // 05001
                $fields['municipality_text'] = ucfirst(strtolower($munName));

                $dep = Department::whereRaw("LPAD(CAST(code AS CHAR), 2, '0') = ?", [$munCode])->first();

                if ($dep) {
                    // Log::info("DEPARTAMENTO ENCONTRADO {$dep->id} - {$dep->name}");
                    $fields['department_id'] = $dep->id;

                    // Municipio (05001)
                    $fullMunCode = $munCode . $munCode3;

                    $mun = Municipality::where('department_id', $dep->id)
                    ->where(function($q) use ($fullMunCode) {
                        $q->whereRaw("LPAD(code, 5, '0') = ?", [$fullMunCode])
                        ->orWhere('code', $fullMunCode)
                        ->orWhere('code', ltrim($fullMunCode, '0'))
                        ->orWhere('code', substr($fullMunCode, 2)); // 001
                    })
                    ->first();

                    if ($mun) {
                        // Log::info("MUNICIPIO ENCONTRADO {$mun->id} - {$mun->name}");
                        $fields['municipality_id'] = $mun->id;
                    } else {
                        Log::info("MUNICIPIO NO ENCONTRADO: $fullMunCode");
                    }

                } else {
                    Log::info("DEPARTAMENTO NO ENCONTRADO EN BD: $munCode");
                }

            } else {
                Log::info("=== NO MATCH UBICACIÓN (POST NORMALIZACIÓN) ===");
            }
        }

        // Matrícula mercantil
        if (preg_match('/Matr[ií]cula mercantil[\s:]*\n?([ \d]{8,30})/iu', $text, $m)) {
            $fields['merchant_registration'] = preg_replace('/\D/', '', $m[1]);
        }

        // Tipo de organización
        $afterIdent = null;
        if (preg_match('/IDENTIFICACI[ÓO]N([\s\S]*?)UBICACI[ÓO]N/iu', $text, $m)) {
            $afterIdent = $m[1];
            Log::info("=== AFTER IDENT REAL ===\n".$afterIdent);
        } else {
            Log::info("NO SE ENCONTRÓ BLOQUE IDENTIFICACIÓN → UBICACIÓN");
        }

        Log::info("=== AFTER IDENT REAL ===\n".$afterIdent);

        if ($afterIdent) {
            // Extraer SOLO la línea 24
            if (preg_match('/24\.\s*Tipo de contribuyente/i', $text, $m, PREG_OFFSET_CAPTURE)) {
                $start = $m[0][1];

                $snippet = substr($text, $start, 350);

                Log::info("=== SNIPPET TIPO CONTRIBUYENTE ===\n".$snippet);

                if (preg_match('/\n\s*([A-Za-zÁÉÍÓÚÑáéíóúñ ]+?)\s+(\d)\b/', $snippet, $tc)) {

                    $orgText = trim($tc[1]);   // Persona jurídica
                    $orgCode = intval($tc[2]); // 1

                    Log::info("TIPO CONTRIBUYENTE DETECTADO: $orgText ($orgCode)");

                    $fields['organization_text'] = $orgText;
                    $fields['organization_code'] = $orgCode;

                    $typeOrg = TypeOrganization::where('code', $orgCode)->first();
                    if ($typeOrg) {
                        $fields['type_organization_id'] = $typeOrg->id;
                    }

                } else {
                    Log::info("NO SE ENCONTRÓ TIPO DE CONTRIBUYENTE EN LÍNEA SIGUIENTE");
                }
            }
        }
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        return response()->json([
            'success' => true,
            'fields' => $fields,
            'raw_text' => $text // Opcional, para debug
        ]);
    }

    // private function ocrRutWithTesseract($pdfPath)
    // {
    //     $poppler = "C:\\Program Files\\poppler-25.11.0\\Library\\bin\\pdftoppm.exe";
    //     $tesseract = "C:\\Program Files\\Tesseract-OCR\\tesseract.exe";

    //     if (!file_exists($tesseract)) {
    //         throw new \Exception("No se encontró Tesseract en: $tesseract");
    //     }

    //     $folder = storage_path("app/rut_temp");
    //     if (!is_dir($folder)) mkdir($folder, 0777, true);

    //     $outputBase = $folder . DIRECTORY_SEPARATOR . "page";

    //     $cmd = "\"$poppler\" -r 300 -png \"$pdfPath\" \"$outputBase\"";
    //     exec($cmd, $output, $returnCode);

    //     if ($returnCode !== 0) {
    //         throw new \Exception("Poppler no pudo convertir el PDF. Código: $returnCode");
    //     }

    //     $images = glob($outputBase . "-*.png");
    //     if (empty($images)) {
    //         throw new \Exception("Poppler no generó imágenes del PDF.");
    //     }

    //     $fullText = "";
    //     foreach ($images as $imagePath) {
    //         $text = (new TesseractOCR($imagePath))
    //             ->executable($tesseract)
    //             // ->lang('spa')
    //             ->psm(6)
    //             ->run();

    //         $fullText .= $text . "\n";
    //     }

    //     return $fullText;
    // }

    // private function cleanOcrText($text)
    // {
    //     $patterns = [
    //         '/º/' => 'o',
    //         '/ª/' => 'a',
    //         '/—/' => '-',
    //         '/[|]/' => '',
    //         '/\s+/' => ' ',
    //     ];

    //     foreach ($patterns as $bad => $good) {
    //         $text = preg_replace($bad, $good, $text);
    //     }

    //     // normaliza acentos
    //     $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

    //     return trim($text);
    // }
    
}
