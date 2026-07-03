<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resolution;
use App\Company;
use App\User;
use App\TypeDocument;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResolutionController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::where('identification_number',$request->company)->first();
        $resolutions = Resolution::where('company_id', $company->id)
            ->with(['type_environment', 'type_document'])
            ->paginate(10);

        // Obtener tipos de documento para el modal
        $typeDocuments = TypeDocument::whereIn('code', ['01', '02', '03', '91', '92', '93', '94', '1', '2', '05', '95'])->get();

        return view('company.resolutions', compact(['resolutions', 'company', 'typeDocuments']));
    }

    /**
     * Store a new resolution
     *
     * @param Request $request
        * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $companyNumber)
    {
        $expectsJson = $request->ajax() || $request->wantsJson() || $request->expectsJson();

        try {
            $rules = [
                'type_document_id' => 'required|exists:type_documents,id',
                'prefix' => 'required|string|max:10',
                'resolution' => 'required|string|max:255',
                'from' => 'nullable|integer|min:0',
                'to' => 'nullable|integer|min:0',
                'resolution_date' => 'nullable|date',
                'technical_key' => 'nullable|string|max:255',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
            ];

            $messages = [
                'type_document_id.required' => 'El tipo de documento es obligatorio.',
                'type_document_id.exists' => 'El tipo de documento seleccionado no es válido.',
                'prefix.required' => 'El prefijo es obligatorio.',
                'prefix.max' => 'El prefijo no puede tener más de 10 caracteres.',
                'resolution.required' => 'El número de resolución es obligatorio.',
            ];

            $request->validate($rules, $messages);

            $company = Company::where('identification_number', $companyNumber)->first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compañía no encontrada.'
                ], 404);
            }

            DB::beginTransaction();

            // El ambiente se toma siempre del actual de la empresa
            // (mismo comportamiento que el endpoint API en facturas).
            $resolutionData = [
                'company_id' => $company->id,
                'type_document_id' => $request->type_document_id,
                'prefix' => $request->prefix,
                'resolution' => $request->resolution,
                'from' => $request->from,
                'to' => $request->to,
                'resolution_date' => $request->resolution_date,
                'technical_key' => $request->technical_key,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'type_environment_id' => $company->type_environment_id,
            ];

            $resolution = Resolution::create($resolutionData);

            DB::commit();

            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Resolución creada exitosamente.',
                    'resolution' => $resolution->load('type_document')
                ]);
            }

            return redirect()->back()->with('success', 'Resolución creada exitosamente.');

        } catch (ValidationException $e) {
            if (!$expectsJson) {
                throw $e;
            }

            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();

            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la resolución: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al crear la resolución: ' . $e->getMessage());
        }
    }

    /**
     * Update a resolution
     *
     * @param Request $request
     * @param int $resolutionId
        * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $companyNumber, $resolutionId)
    {
        $expectsJson = $request->ajax() || $request->wantsJson() || $request->expectsJson();

        try {
            $resolution = Resolution::findOrFail($resolutionId);
            $company = Company::where('identification_number', $companyNumber)->first();

            if (!$company || $resolution->company_id !== $company->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resolución no encontrada o no pertenece a la compañía.'
                ], 404);
            }

            $rules = [
                'type_document_id' => 'required|exists:type_documents,id',
                'resolution' => 'required|string|max:255',
                'from' => 'nullable|integer|min:0',
                'to' => 'nullable|integer|min:0',
                'resolution_date' => 'nullable|date',
                'technical_key' => 'nullable|string|max:255',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
            ];

            $messages = [
                'type_document_id.required' => 'El tipo de documento es obligatorio.',
                'type_document_id.exists' => 'El tipo de documento seleccionado no es válido.',
                'resolution.required' => 'El número de resolución es obligatorio.',
            ];

            $request->validate($rules, $messages);

            DB::beginTransaction();

            $updateData = [
                'type_document_id' => $request->type_document_id,
                'resolution' => $request->resolution,
                'from' => $request->from,
                'to' => $request->to,
                'resolution_date' => $request->resolution_date,
                'technical_key' => $request->technical_key,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ];

            $resolution->update($updateData);

            DB::commit();

            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Resolución actualizada exitosamente.',
                    'resolution' => $resolution->load('type_document')
                ]);
            }

            return redirect()->back()->with('success', 'Resolución actualizada exitosamente.');

        } catch (ValidationException $e) {
            if (!$expectsJson) {
                throw $e;
            }

            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();

            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la resolución: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al actualizar la resolución: ' . $e->getMessage());
        }
    }

    /**
     * Update resolution environment
     *
     * @param Request $request
     * @param int $resolutionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateEnvironment(Request $request)
    {
        DB::beginTransaction();

        try {
            $resolution = Resolution::findOrFail($request->resolutionId);

            // Verificar que la resolución pertenezca a la compañía del usuario autenticado
            $company = Company::where('identification_number', $request->company)->first();

            // Actualizar el tipo de entorno de la resolución con el de la compañía
            $resolution->update([
                'type_environment_id' => $company->type_environment_id,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Entorno de la resolución actualizado con éxito');

        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error al actualizar la resolución: ' . $e->getMessage());
        }
    }

    public function create(Request $request, $companyNumber)
    {
        $company = Company::where('identification_number', $companyNumber)->firstOrFail();
        $typeDocuments = TypeDocument::whereIn('code', ['01', '02', '03', '91', '92', '93', '94', '1', '2', '05', '95'])->get();

        // Recoge los parámetros GET para prellenar
        $prefill = $request->only([
            'prefix', 'resolution', 'resolution_date', 'from', 'to', 'date_from', 'date_to', 'technical_key'
        ]);

        // Consulta las resoluciones existentes para la tabla
        $resolutions = \App\Resolution::where('company_id', $company->id)
            ->with(['type_environment', 'type_document'])
            ->paginate(10);

        return view('company.resolutions', compact('company', 'typeDocuments', 'prefill', 'resolutions'));
    }
}
