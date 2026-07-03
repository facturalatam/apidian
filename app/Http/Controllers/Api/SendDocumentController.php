<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Traits\DocumentTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendDocumentRequest;
use ubl21dian\XAdES\SignInvoice;
use ubl21dian\XAdES\SignCreditNote;
use ubl21dian\XAdES\SignDebitNote;
use ubl21dian\Templates\SOAP\SendBillAsync;
use ubl21dian\Templates\SOAP\SendBillSync;
use ubl21dian\Templates\SOAP\SendTestSetAsync;
use App\Services\StorageService;

class SendDocumentController extends Controller
{
    use DocumentTrait;

    /**
     * Store.
     *
     * @param \App\Http\Requests\Api\SendDocumentRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function SendDocument(SendDocumentRequest $request)
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
        StorageService::ensureDirectory("certificates");
        $certPath = StorageService::tempPath("certificates/{$name}");
        file_put_contents($certPath, $certificateBinary);

        // Create XML
        $invoice = base64_decode($request->documentbase64);

        // Signature XML
        if($request->tipodoc == 'INVOICE')
            $SendDocument = new SignInvoice($certPath, $request->password);
        else
            if($request->tipodoc == 'NC')    
                $SendDocument = new SignCreditNote($certPath, $request->password);
            else
                if($request->tipodoc == 'ND')
                    $SendDocument = new SignDebitNote($certPath, $request->password);
                else    
                    return [
                        'message' => "El tipo de documento {$request->tipodoc} no es soportado por esta peticion",
                        'success' => 'false'
                    ];
    
        $SendDocument->softwareID = $request->softwareid;
        $SendDocument->pin = $request->pin;
        if($request->tipodoc == 'INVOICE')
            $SendDocument->technicalKey = $request->technicalKey;

        StorageService::ensureDirectory("public/{$request->password}");

        $SendDocument->GuardarEn = StorageService::tempPath("public/{$request->password}/DOC-{$request->documentnumber}.xml");
        $file = fopen(StorageService::tempPath("public/{$request->password}/DOCS-{$request->documentnumber}.xml"), "w");
        fwrite($file, $SendDocument->sign($invoice)->xml);
        fclose($file);

        if($request->ambiente == 'HABILITACION')
        {
            $sendTestSetAsync = new SendTestSetAsync($certPath, $request->password);
            $sendTestSetAsync->To = 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc';
            $sendTestSetAsync->fileName = "{$request->documentnumber}.xml";
            $sendTestSetAsync->contentFile = $this->zipBase64SendDocument($request->password, $request->identificationnumber, $request->tipodoc, $request->documentnumber, $SendDocument->sign($invoice), StorageService::tempPath("public/{$request->password}/DOCS-{$request->documentnumber}"));
            $sendTestSetAsync->testSetId = $request->testSetID;
        }
        else
            if($request->ambiente == 'PRODUCCION')
            {
                $sendBillSync = new SendBillSync($certPath, $request->password);
                $sendBillSync->To = 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc';
                $sendBillSync->fileName = "{$request->documentnumber}.xml";
                $sendBillSync->contentFile = $this->zipBase64SendDocument($request->password, $request->identificationnumber, $request->tipodoc, $request->documentnumber, $SendDocument->sign($invoice), StorageService::tempPath("public/{$request->password}/DOCS-{$request->documentnumber}"));
            }
            else
                return [
                    'message' => "El ambiente de trabajo {$request->ambiente} no es valido para esta peticion",
                    'success' => 'false'
                ];

        // Upload to S3 if configured
        StorageService::uploadBatchIfS3([
            "public/{$request->password}/DOC-{$request->documentnumber}.xml",
            "public/{$request->password}/DOCS-{$request->documentnumber}.xml",
            "public/{$request->password}/DOCS-{$request->documentnumber}.zip",
            "public/{$request->password}/ReqDOC-{$request->documentnumber}.xml",
            "public/{$request->password}/RptaDOC-{$request->documentnumber}.xml",
        ]);
        
        if($request->tipodoc == 'INVOICE')
            if($request->ambiente == 'PRODUCCION')
                return [
                    'message' => "El documento Nro {$request->documentnumber} firmado y enviado con éxito",
                    'ResponseDian' => $sendBillSync->signToSend(StorageService::tempPath("public/{$request->password}/ReqDOC-{$request->documentnumber}.xml"))->getResponseToObject(StorageService::tempPath("public/{$request->password}/RptaDOC-{$request->documentnumber}.xml")),
                    'invoicexml'=>StorageService::getBase64Auto("public/{$request->password}/DOCS-{$request->documentnumber}.xml"),
                    'cufe' => $SendDocument->ConsultarCUFE()
                ];
            else    
                return [
                    'message' => "El documento Nro {$request->documentnumber} firmado y enviado con éxito",
                    'ResponseDian' => $sendTestSetAsync->signToSend(StorageService::tempPath("public/{$request->password}/ReqDOC-{$request->documentnumber}.xml"))->getResponseToObject(StorageService::tempPath("public/{$request->password}/RptaDOC-{$request->documentnumber}.xml")),
                    'invoicexml'=>StorageService::getBase64Auto("public/{$request->password}/DOCS-{$request->documentnumber}.xml"),
                    'cufe' => $SendDocument->ConsultarCUFE()
                ];
        else
            if($request->ambiente == 'PRODUCCION')
                return [
                    'message' => "El documento Nro {$request->documentnumber} firmado y enviado con éxito",
                    'ResponseDian' => $sendBillSync->signToSend(StorageService::tempPath("public/{$request->password}/ReqDOC-{$request->documentnumber}.xml"))->getResponseToObject(StorageService::tempPath("public/{$request->password}/RptaDOC-{$request->documentnumber}.xml")),
                    'invoicexml'=>StorageService::getBase64Auto("public/{$request->password}/DOCS-{$request->documentnumber}.xml"),
                    'cude' => $SendDocument->ConsultarCUDE()
                ];
            else
                return [
                    'message' => "El documento Nro {$request->documentnumber} firmado con éxito",
                    'ResponseDian' => $sendTestSetAsync->signToSend(StorageService::tempPath("public/{$request->password}/ReqDOC-{$request->documentnumber}.xml"))->getResponseToObject(StorageService::tempPath("public/{$request->password}/RptaDOC-{$request->documentnumber}.xml")),
                    'invoicexml'=>StorageService::getBase64Auto("public/{$request->password}/DOCS-{$request->documentnumber}.xml"),
                    'cude' => $SendDocument->ConsultarCUDE()
                ];

    }
}
