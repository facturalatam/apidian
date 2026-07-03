<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use ubl21dian\Templates\SOAP\GetXmlByDocumentKey;
use App\Http\Requests\Api\XmlDocumentRequest;
use App\Traits\DocumentTrait;
use App\Services\StorageService;

class XmlDocumentController extends Controller
{
    use DocumentTrait;

    /**
     * Document.
     *
     * @param string $trackId
     *
     * @return array
     */
    public function document(XmlDocumentRequest $request, $trackId, $GuardarEn = false, $companyOverride = null)
    {
        // User
        if($companyOverride){
            $company = $companyOverride;
            $user = \App\User::where('id', $company->user_id)->firstOrFail();
        }
        else{
            $user = auth()->user();
            $company = $user->company;
        }

        // Verificar la disponibilidad de la DIAN antes de continuar
        if (isset($request->is_event) && $request->is_event) {
            $dian_url = $company->software->url_event;
        } elseif ($request->is_payroll) {
            $dian_url = $company->software->url_payroll;
        } else {
            $dian_url = $company->software->url;
        }        
        if (!$this->verificarEstadoDIAN($dian_url)) {
            // Manejar la indisponibilidad del servicio, por ejemplo:
            return [
                'success' => false,
                'message' => 'El servicio de la DIAN no está disponible en este momento. Por favor, inténtelo más tarde.',
            ];
        }

        // Verify Certificate
        $certificate_days_left = 0;
        $c = $this->verify_certificate($user);
        if(!$c['success'])
            return $c;
        else
            $certificate_days_left = $c['certificate_days_left'];

        if ($request->is_payroll) {
            $getXml = new GetXmlByDocumentKey($user->company->certificate->path, $user->company->certificate->password, $company->software->url_payroll);
        } elseif (isset($request->is_event) && $request->is_event) {
            $getXml = new GetXmlByDocumentKey($user->company->certificate->path, $user->company->certificate->password, $company->software->url_event);
        } else {
            $getXml = new GetXmlByDocumentKey($user->company->certificate->path, $user->company->certificate->password, $company->software->url);
        }
        $getXml->trackId = $trackId;
        $GuardarEn = str_replace("_", "\\", $GuardarEn);

        if ($request->GuardarEn){
            $R = $getXml->signToSend($request->GuardarEn.'\\Req-XmlDocument.xml')->getResponseToObject($request->GuardarEn.'\\Rpta-XmlDocument.xml');
            if($R->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->Code == "100")
                return [
                    'success' => true,
                    'message' => 'Consulta generada con éxito',
                    'ResponseDian' => $R,
                    'certificate_days_left' => $certificate_days_left,
                ];
            else
                return [
                    'success' => false,
                    'message' => 'Consulta generada con éxito',
                    'ResponseDian' => $R->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->Message,
                    'certificate_days_left' => $certificate_days_left,
                ];
        }
        else{
            $R = $getXml->signToSend()->getResponseToObject();
            if($R->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->Code == "100")
                return [
                    'success' => true,
                    'message' => 'Consulta generada con éxito',
                    'ResponseDian' => $R,
                    'certificate_days_left' => $certificate_days_left,
                ];
            else
                return [
                    'success' => false,
                    'message' => 'Consulta generada con éxito',
                    'ResponseDian' => $R->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->Message,
                    'certificate_days_left' => $certificate_days_left,
                ];
        }
    }
}
