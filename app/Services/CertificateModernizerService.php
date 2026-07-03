<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class CertificateModernizerService
{
    /**
     * Convierte un certificado legacy a moderno usando el API externo.
     * @param string $certBase64
     * @param string $password
     * @return string|null Nuevo certificado en base64 o null si falla
     * @throws Exception Si la conversión falla
     */
    public function convertLegacyToModern($certBase64, $password)
    {
        \Log::info('Iniciando conversión de certificado legacy a moderno.');
        $url_base = config('system_configuration.url_api_cert_modernizer');
        if (!$url_base) {
            throw new Exception('No está configurada la URL del modernizador de certificados.');
        }

        $url = $url_base . '/modernizer-b64';

        $response = Http::timeout(15)
            ->acceptJson()
            ->post($url, [
                'cert' => $certBase64,
                'password' => $password,
            ]);
        if ($response->successful() && $response->json('status') === true && $response->json('cert')) {
            return $response->json('cert');
        }

        $msg = $response->json('detail') ?? $response->body();
        throw new Exception('Error al convertir certificado legacy: ' . json_encode($msg));
    }
}
