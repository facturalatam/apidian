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
use App\Services\RutExtractorV2;


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

    /**
     * Extrae los datos de la ficha RUT (PDF) para precargar el formulario
     * de empresa. Delegado a RutExtractorV2 (parser anclado a las casillas
     * del formulario DIAN 001 sobre el texto de pdftotext -layout).
     */
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

        $destination = storage_path('app/rut_temp');
        if (!is_dir($destination)) mkdir($destination, 0777, true);

        $filename = uniqid().'.pdf';
        $file->move($destination, $filename);
        $fullPath = $destination.'/'.$filename;

        try {
            $result = RutExtractorV2::extract($fullPath);

            return response()->json([
                'success' => true,
                'fields' => $result['fields'],
                'raw_text' => $result['raw_text'] // Opcional, para debug
            ]);
        } catch (\Throwable $e) {
            Log::error('extractRut: '.$e->getMessage());

            // 200 con success=false para que el front muestre el mensaje real
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } finally {
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

}
