<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\PaymentForm;
use App\PaymentMethod;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function getPaymentMethods()
    {
        $payment_methods = PaymentMethod::all();
        return compact('payment_methods');
    }

    public function getPaymentForms()
    {
        $payment_forms = PaymentForm::all();
        return compact('payment_forms');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        $payment_method = PaymentMethod::create($data);

        return response()->json([
            'success' => true,
            'payment_method' => $payment_method,
            'id' => $payment_method->id
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $payment_method = PaymentMethod::find($id);
        if (! $payment_method) {
            return response()->json(['success' => false, 'message' => 'Método de pago no encontrado'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        $payment_method->update($data);

        return response()->json(['success' => true, 'payment_method' => $payment_method]);
    }

    public function destroy($id)
    {
        $payment_method = PaymentMethod::find($id);
        if (! $payment_method) {
            return response()->json(['success' => false, 'message' => 'Método de pago no encontrado'], 404);
        }

        $payment_method->delete();

        return response()->json(['success' => true, 'message' => 'Método de pago eliminado']);
    }

    public function destroyByFields(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
        ]);

        $deleted = PaymentMethod::where('name', $data['name'])
            ->where('code', $data['code'])
            ->delete();

        if ($deleted === 0) {
            return response()->json(['success' => false, 'message' => 'Método de pago no encontrado'], 404);
        }

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}
