<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\WhatsappConfig;
use Illuminate\Support\Facades\Http;
use App\User;
use Illuminate\Http\Request;

class WhatsappConfigController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'url_whatsapp' => 'required|url',
            'token_whatsapp' => 'required|string',
        ]);

        $token = $request->bearerToken();
        $user = User::where('api_token', $token)->firstOrFail();

        $config = WhatsappConfig::updateOrCreate(
            ['user_id' => $user->id],
            [
                'url_whatsapp' => $request->url_whatsapp,
                'token_whatsapp' => $request->token_whatsapp,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Configuración de WhatsApp guardada correctamente',
            'data' => $config
        ]);
    }

    public function show(Request $request)
    {
        // Obtener el usuario a partir del token de autorización
        $token = $request->bearerToken();
        $user = User::where('api_token', $token)->firstOrFail();
        
        $config = WhatsappConfig::where('user_id', $user->id)->first();
        
        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'number' => 'required|string',
                'message' => 'required|string'
            ]);

            $token = $request->bearerToken();
            $user = User::where('api_token', $token)->firstOrFail();
            $config = WhatsappConfig::where('user_id', $user->id)->firstOrFail();

            // Formatear número con prefijo de Colombia
            $formattedNumber = $this->formatPhoneNumber($request->number);

            // Preparar datos
            $data = [
                'number' => $formattedNumber,
                'message' => $request->message
            ];

            // Inicializar cURL
            $ch = curl_init($config->url_whatsapp . '/api/message/send-text');
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $config->token_whatsapp,
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                throw new \Exception('Error cURL: ' . curl_error($ch));
            }
            
            curl_close($ch);

            $responseData = json_decode($response, true);

            if ($httpCode >= 200 && $httpCode < 300) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar mensaje',
                'error' => $responseData
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function sendMessageWithPDF(Request $request)
    {
        try {
            $request->validate([
                'number' => 'required|string',
                'message' => 'required|string',
                'file' => 'required|string',
                'filename' => 'required|string',
                'send_whatsapp' => 'required|boolean'
            ]);

            if (!$request->send_whatsapp) {
                return response()->json([
                    'success' => true,
                    'message' => 'No se requirió envío por WhatsApp'
                ]);
            }

            $token = $request->bearerToken();
            $user = User::where('api_token', $token)->firstOrFail();
            $config = WhatsappConfig::where('user_id', $user->id)->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró configuración de WhatsApp para este usuario. Por favor configure primero.'
                ], 400);
            }

            // Formatear número con prefijo de Colombia
            $formattedNumber = $this->formatPhoneNumber($request->number);

            // Preparar datos para la petición
            $data = [
                'number' => $formattedNumber,
                'message' => $request->message,
                'file' => $request->file,
                'filename' => $request->filename
            ];

            // Inicializar cURL
            $ch = curl_init($config->url_whatsapp . '/api/message/send/pdf');
            
            // Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $config->token_whatsapp,
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo para desarrollo

            // Ejecutar petición
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Verificar errores
            if (curl_errno($ch)) {
                throw new \Exception('Error cURL: ' . curl_error($ch));
            }
            
            curl_close($ch);

            // Procesar respuesta
            $responseData = json_decode($response, true);

            if ($httpCode >= 200 && $httpCode < 300) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar mensaje con PDF',
                'error' => $responseData
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    private function formatPhoneNumber($number)
    {
        // Eliminar cualquier caracter que no sea número
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // Si ya tiene el prefijo 57, no lo agregamos
        if (substr($number, 0, 2) === '57') {
            return $number;
        }
        
        // Agregar prefijo 57
        return '57' . $number;
    }
}