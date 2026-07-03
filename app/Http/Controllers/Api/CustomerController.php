<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Customer;
use Illuminate\Http\Request;
use App\Custom\GetAdquirerRequest;

class CustomerController extends Controller
{
    public function records()
    {
        try {
            $companyId = auth()->user()->company->id;
            $search = request()->query('search');

            $query = Customer::where('companies_id', $companyId)
                ->select('identification_number', 'dv', 'name', 'phone', 'address', 'email', 'companies_id');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('identification_number', 'like', "%$search%")
                      ->orWhere('name', 'like', "%$search%");
                });
            }

            $customers = $query->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $customers
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
        try {
            $validatedData = $request->validate([
                'identification_number' => 'required|string|max:20|unique:customers',
                'dv' => 'nullable|string|max:1',
                'name' => 'required|string|max:500',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'email' => 'nullable|email|max:255',
                // 'password' => 'required|string|min:6',
                'companies_id' => 'required|exists:companies,id'
            ]);

            $validatedData['password'] = bcrypt($validatedData['identification_number']);
            $customer = Customer::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'data' => [
                    'customer' => [
                        'name' => $customer->name,
                        'email' => $customer->email
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $identification_number)
    {
        try {
            // Buscar el cliente por número de documento
            $customer = Customer::where('identification_number', $identification_number)->firstOrFail();

            // Verificar que el cliente pertenezca a la empresa del usuario autenticado
            $companyId = auth()->user()->company->id;
            if ($customer->companies_id !== $companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para actualizar este cliente'
                ], 403);
            }

            $validatedData = $request->validate([
                'dv' => 'nullable|numeric|digits:1',
                'name' => 'required|string|max:500',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'email' => 'nullable|email|max:255',
            ], [
                'dv.numeric' => 'El dígito de verificación debe ser numérico.',
                'dv.digits' => 'El dígito de verificación debe ser de 1 dígito.',
                'name.required' => 'El nombre es obligatorio.',
                'name.string' => 'El nombre debe ser texto.',
                'name.max' => 'El nombre no puede tener más de 500 caracteres.',
                'phone.string' => 'El teléfono debe ser texto.',
                'phone.max' => 'El teléfono no puede tener más de 20 caracteres.',
                'address.string' => 'La dirección debe ser texto.',
                'address.max' => 'La dirección no puede tener más de 500 caracteres.',
                'email.email' => 'El email debe tener un formato válido.',
                'email.max' => 'El email no puede tener más de 255 caracteres.',
            ]);

            $customer->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'identification_number' => $customer->identification_number,
                        'dv' => $customer->dv,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'address' => $customer->address,
                        'email' => $customer->email
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
                'message' => 'Cliente no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAcquirer($document_type_identification_id, $document_number)
    {
        $response = $this->createXML($document_type_identification_id, $document_number);

        try {
            if (isset($response->Envelope->Body->Fault)) {
                $fault = $response->Envelope->Body->Fault;
                $faultString = isset($fault->Reason->Text) ? (string)$fault->Reason->Text['_value'] : 'Unknown error';
                $faultCode = isset($fault->Code->Value) ? (string)$fault->Code->Value : 'Unknown code';

                return response()->json([
                    'success' => false,
                    'message' => 'Dian Error: ' . $faultString,
                    'code' => $faultCode
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing Dian response',
                'error' => $e->getMessage()
            ], 500);
        }
        $status = $response->Envelope->Body->GetAcquirerResponse->GetAcquirerResult->StatusCode;
        $message = $response->Envelope->Body->GetAcquirerResponse->GetAcquirerResult->Message;
        if($status === '404') {
            return [
                'success' => false,
                'message' => $message,
                'status' => $status
            ];
        }
        return [
            'success' => true,
            'message' => $message,
            'ResponseDian' => $response->Envelope->Body,
            'status' => $status
        ];
    }

    protected function createXML($document_type_identification_id, $document_number)
    {
        $company = auth()->user()->company;
        $getAdquirerRequest = new GetAdquirerRequest($company->certificate->path, $company->certificate->password);
        $getAdquirerRequest->identificationType = $document_type_identification_id;
        $getAdquirerRequest->identificationNumber = $document_number;
        $getAdquirerRequest->To = $company->software->url;
        $respuestadian = $getAdquirerRequest->signToSend()->getResponseToObject();

        return $respuestadian;
    }
}
