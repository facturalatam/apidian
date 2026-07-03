<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Traits\DocumentTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SignDocumentRequest;
use ubl21dian\XAdES\SignInvoice;
use ubl21dian\XAdES\SignCreditNote;
use ubl21dian\XAdES\SignDebitNote;
use App\Services\StorageService;

class SignDocumentController extends Controller
{
    use DocumentTrait;

    /**
     * Store.
     *
     * @param \App\Http\Requests\Api\SignDocumentRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function signdocument(SignDocumentRequest $request)
    {
        try {
            if (!base64_decode($request->certificate, true)) {
                throw new Exception('The given data of the certificate was invalid.');
            }
            if (!base64_decode($request->documentbase64, true)) {
                throw new Exception('The given data of the document was invalid.');
            }
            if (!openssl_pkcs12_read($certificateBinary = base64_decode($request->certificate), $certificate, $request->password)) {
                throw new Exception('The certificate could not be read.');
            }
        } catch (Exception $e) {
            if (false == ($error = openssl_error_string())) {
                return response([
                    'message' => $e->getMessage(),
                    'errors' => [
                        'errors' => 'The base64 encoding is not valid.',
                    ],
                ], 422);
            }

            return response([
                'message' => $e->getMessage(),
                'errors' => [
                    'certificate' => $error,
                    'password' => $error,
                ],
            ], 422);
        }

        $name = "{$request->password}.p12";
        // Certificate must be stored locally for OpenSSL signing
        $certPath = StorageService::tempPath("certificates/{$name}");
        StorageService::ensureDirectory("certificates");
        file_put_contents($certPath, $certificateBinary);

        // Create XML
        $invoice = base64_decode($request->documentbase64);

        // Signature XML
        if($request->tipodoc == 'INVOICE')
            $signDocument = new SignInvoice($certPath, $request->password);
        else
            if($request->tipodoc == 'NC')    
                $signDocument = new SignCreditNote($certPath, $request->password);
            else
                if($request->tipodoc == 'ND')
                    $signDocument = new SignDebitNote($certPath, $request->password);
                else    
                    return [
                        'message' => "El tipo de documento {$request->tipodoc} no es soportado por esta peticion",
                        'success' => 'false'
                    ];
    
        $signDocument->softwareID = $request->softwareid;
        $signDocument->pin = $request->pin;
        if($request->tipodoc == 'INVOICE')
            $signDocument->technicalKey = $request->technicalKey;

        StorageService::ensureDirectory("public/{$request->password}");

        $signDocument->GuardarEn = StorageService::tempPath("public/{$request->password}/DOC-{$request->documentnumber}.xml");
        $file = fopen(StorageService::tempPath("public/{$request->password}/DOCS-{$request->documentnumber}.xml"), "w");
        fwrite($file, $signDocument->sign($invoice)->xml);
        fclose($file);

        // Upload to S3 if configured
        StorageService::uploadBatchIfS3([
            "public/{$request->password}/DOC-{$request->documentnumber}.xml",
            "public/{$request->password}/DOCS-{$request->documentnumber}.xml",
        ]);
        
        if($request->tipodoc == 'INVOICE')
            return [
                'message' => "El documento Nro {$request->documentnumber} firmado con éxito",
                'invoicexml'=>StorageService::getBase64Auto("public/{$request->password}/DOCS-{$request->documentnumber}.xml"),
                'cufe' => $signDocument->ConsultarCUFE()
            ];
        else
            return [
                'message' => "El documento Nro {$request->documentnumber} firmado con éxito",
                'invoicexml'=>StorageService::getBase64Auto("public/{$request->password}/DOCS-{$request->documentnumber}.xml"),
                'cude' => $signDocument->ConsultarCUDE()
            ];
    }
}
