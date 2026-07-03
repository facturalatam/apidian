<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Adicionales Facturador
            'ivaresponsable' => 'nullable|string',
            'nombretipodocid' => 'nullable|string',
            'tarifaica' => 'nullable|string',
            'actividadeconomica' => 'nullable|string',

            // Datos del Establecimiento
            'establishment_name' => 'nullable|string',
            'establishment_address' => 'nullable|string',
            'establishment_phone' => 'nullable|numeric|digits_between:7,10',
            'establishment_municipality' => 'nullable|exists:municipalities,id',
            'establishment_email' => 'nullable|string|email',
            'establishment_logo' => 'nullable|string',

            // Lista de correos a enviar copia
            'email_cc_list' => 'nullable|array',
            'email_cc_list.*.email' => 'nullable|required_with:email_cc_list,|string|email',

            // Enviar Correo al receptor
            'sendmail' => 'nullable|boolean',
            'sendmailtome' => 'nullable|boolean',

            // ID Event
            'event_id' => 'required|exists:events,id',

            // Attached Document Base 64
            'base64_attacheddocument' => 'required|string',
            'base64_attacheddocument_name' => 'required|string',

            // Issuer Party
            'issuer_party' => 'nullable|array',
            'issuer_party.identification_number' => 'nullable|required_with:issuer_party|string',
            'issuer_party.first_name' => 'nullable|required_with:issuer_party|string',
            'issuer_party.last_name' => 'nullable|required_with:issuer_party|string',
            'issuer_party.organization_department' => 'nullable|required_with:issuer_party|string',
            'issuer_party.job_title' => 'nullable|required_with:issuer_party|string',

            // Type Rejection
            'type_rejection_id' => 'nullable|required_if:event_id,2|exists:type_rejections,id',

            // Re send consecutive
            'resend_consecutive' => 'nullable|boolean'
        ];
    }
}
