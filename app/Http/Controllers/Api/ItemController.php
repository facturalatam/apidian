<?php

namespace App\Http\Controllers\Api;

use App\Item;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function records()
    {
        try {
            $companyId = auth()->user()->company->id;
            $search = request()->query('search');

            $query = Item::where('companies_id', $companyId)
                        ->select('id', 'name', 'second_name', 'description', 'internal_id', 'sale_unit_price', 'percentage_of_profit', 'is_set', 'has_perception', 'percentage_perception','companies_id', 'status');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('internal_id', 'like', "%$search%");
                });
            }

            $items = $query->get();

            return response()->json([
                'success' => true,
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:500',
            'second_name' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:500',
            'item_type_id' => 'required|string|max:2',
            'internal_id' => 'nullable|string|max:100',
            'date_of_due' => 'nullable|date',
            'sale_unit_price' => 'required|numeric',
            'purchase_unit_price' => 'nullable|numeric',
            'commission_amount' => 'nullable|numeric',
            'commission_type' => 'nullable|string|max:255',
            'amount_plastic_bag_taxes' => 'nullable|numeric',
            'calculate_quantity' => 'nullable|boolean',
            'sale_unit_price_set' => 'nullable|numeric',
            'is_set' => 'nullable|boolean',
            'model' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'image_medium' => 'nullable|string|max:255',
            'image_small' => 'nullable|string|max:255',
            'lot_code' => 'nullable|string|max:255',
            'lots_enabled' => 'nullable|boolean',
            'series_enabled' => 'nullable|boolean',
            'percentage_of_profit' => 'nullable|numeric',
            'has_perception' => 'nullable|boolean',
            'percentage_perception' => 'nullable|numeric',
            'attributes' => 'nullable|json',
            'active' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'apply_store' => 'nullable|boolean',
            'companies_id' => 'required|integer|exists:companies,id',
        ]);

        $item = Item::create($validatedData);

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        try {
            // Buscar el item por ID
            $item = Item::findOrFail($id);

            // Verificar que el item pertenezca a la empresa del usuario autenticado
            $companyId = auth()->user()->company->id;
            if ($item->companies_id !== $companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para actualizar este item'
                ], 403);
            }

            $validatedData = $request->validate([
                'name' => 'required|string|max:500',
                'description' => 'nullable|string|max:500',
                'internal_id' => 'nullable|string|max:100',
                'sale_unit_price' => 'required|numeric|min:0',
                'percentage_of_profit' => 'nullable|numeric|min:0|max:100',
                'is_set' => 'nullable|boolean',
                'has_perception' => 'nullable|boolean',
                'percentage_perception' => 'nullable|numeric|min:0|max:100',
                'status' => 'nullable|boolean',
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'name.string' => 'El nombre debe ser texto.',
                'name.max' => 'El nombre no puede tener más de 500 caracteres.',
                'description.string' => 'La descripción debe ser texto.',
                'description.max' => 'La descripción no puede tener más de 500 caracteres.',
                'internal_id.string' => 'El ID interno debe ser texto.',
                'internal_id.max' => 'El ID interno no puede tener más de 100 caracteres.',
                'sale_unit_price.required' => 'El precio de venta es obligatorio.',
                'sale_unit_price.numeric' => 'El precio de venta debe ser numérico.',
                'sale_unit_price.min' => 'El precio de venta debe ser mayor o igual a 0.',
                'percentage_of_profit.numeric' => 'El porcentaje de ganancia debe ser numérico.',
                'percentage_of_profit.min' => 'El porcentaje de ganancia debe ser mayor o igual a 0.',
                'percentage_of_profit.max' => 'El porcentaje de ganancia no puede ser mayor a 100.',
                'is_set.boolean' => 'El campo es set debe ser verdadero o falso.',
                'has_perception.boolean' => 'El campo tiene percepción debe ser verdadero o falso.',
                'percentage_perception.numeric' => 'El porcentaje de percepción debe ser numérico.',
                'percentage_perception.min' => 'El porcentaje de percepción debe ser mayor o igual a 0.',
                'percentage_perception.max' => 'El porcentaje de percepción no puede ser mayor a 100.',
                'status.boolean' => 'El estado debe ser verdadero o falso.',
            ]);

            $item->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Item actualizado exitosamente',
                'data' => [
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'second_name' => $item->second_name,
                        'description' => $item->description,
                        'internal_id' => $item->internal_id,
                        'sale_unit_price' => $item->sale_unit_price,
                        'percentage_of_profit' => $item->percentage_of_profit,
                        'is_set' => $item->is_set,
                        'has_perception' => $item->has_perception,
                        'percentage_perception' => $item->percentage_perception,
                        'status' => $item->status,
                        'companies_id' => $item->companies_id
                    ]
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Item no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
