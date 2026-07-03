<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Municipality;
use Illuminate\Http\Request;

class MunicipalityController extends Controller
{
    /**
     * Dado un codefacturador (proveniente de un sistema externo), retorna el código DIAN del municipio.
     */
    public function codeByFacturador(Request $request, $codefacturador)
    {
        // Normalizar por si viene con espacios
        $codefacturador = trim((string) $codefacturador);

        if ($codefacturador === '') {
            return response()->json([
                'success' => false,
                'message' => 'El codefacturador es requerido.',
            ], 422);
        }

        $municipality = Municipality::query()
            ->without('department')
            ->select(['id', 'code', 'codefacturador', 'name', 'department_id'])
            ->where('codefacturador', $codefacturador)
            ->first();

        if (! $municipality) {
            return response()->json([
                'success' => false,
                'message' => 'Municipio no encontrado para el codefacturador proporcionado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'codefacturador' => $municipality->codefacturador,
            'municipality_code' => $municipality->code,
            'municipality_id' => $municipality->id,
            'municipality_name' => $municipality->name,
        ]);
    }

    /**
     * Dado un listado de codefacturadores, retorna un mapa (key = codefacturador)
     * con el código DIAN del municipio y metadatos.
     *
     * POST /api/table/municipality-codes-by-facturador
     * { "codefacturadores": ["1006","1007","1200"] }
     */
    public function codesByFacturador(Request $request)
    {
        $input = $request->input('codefacturadores');

        if (! is_array($input)) {
            return response()->json([
                'success' => false,
                'message' => 'El campo codefacturadores debe ser un arreglo.',
            ], 422);
        }

        $ids = [];
        foreach ($input as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                $ids[] = $value;
            }
        }

        if (count($ids) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'El campo codefacturadores es requerido.',
            ], 422);
        }

        $idsForQuery = array_values(array_unique($ids));

        $rows = Municipality::query()
            ->without('department')
            ->select(['id', 'code', 'codefacturador', 'name', 'department_id'])
            ->whereIn('codefacturador', $idsForQuery)
            ->get();

        $data = [];
        foreach ($rows as $m) {
            $data[(string) $m->codefacturador] = [
                'codefacturador' => (string) $m->codefacturador,
                'municipality_code' => (string) $m->code,
                'municipality_id' => (int) $m->id,
                'municipality_name' => (string) $m->name,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'requested' => $ids,
            'missing' => array_values(array_diff($ids, array_keys($data))),
        ]);
    }
}
