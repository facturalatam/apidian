<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendEventDataRequest extends FormRequest
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
            'send_email_cc_list_as_email_cc' => 'nullable|boolean',

            // Enviar Correo al receptor
            'validate_email' => 'nullable|boolean',
            'sendmail' => 'nullable|boolean',
            'sendmailtome' => 'nullable|boolean',

            // ID Event
            'event_id' => 'required|exists:events,id',

//            // Customer
////            'customer' => 'nullable|required_without:seller|array',
//            'customer' => 'nullable|array',
//            'customer.identification_number' => 'required_with:customer|alpha_num|between:1,15',
//            'customer.type_document_identification_id' => 'required_with:customer|exists:type_document_identifications,id',
//            'customer.type_organization_id' => 'required_with:customer|exists:type_organizations,id',
//            'customer.type_regime_id' => 'required_with:customer|exists:type_regimes,id',
//            'customer.tax_id' => 'required_with:customer|exists:taxes,id',
//            'customer.type_liability_id' => 'required_with:customer|exists:type_liabilities,id',
//            'customer.name' => 'required_with:customer|string',
//            'customer.email' => 'required_with:customer|string|email',

//            // Seller
////            'seller' => 'nullable|required_without:customer|array',
//            'seller' => 'nullable|array',
//            'seller.identification_number' => 'required_with:seller|alpha_num|between:1,15',
//            'seller.type_document_identification_id' => 'required_with:seller|exists:type_document_identifications,id',
//            'seller.type_organization_id' => 'required_with:seller|exists:type_organizations,id',
//            'seller.type_regime_id' => 'required_with:seller|exists:type_regimes,id',
//            'seller.tax_id' => 'required_with:seller|exists:taxes,id',
//            'seller.type_liability_id' => 'required_with:seller|exists:type_liabilities,id',
//            'seller.name' => 'required_with:seller|string',
//            'seller.email' => 'required_with:seller|string|email',

            // Document Reference
            'document_reference' => 'required|array',
//            'document_reference.prefix' => 'nullable|alpha_num|between:1,4',
//            'document_reference.number' => 'nullable|integer',
            'document_reference.cufe' => 'required|string',
//            'document_reference.issue_date' => 'nullable|date_format:Y-m-d H:i:s',
//            'document_reference.type_document_id' => 'nullable|in:1,2,3,12|exists:type_documents,id',
//            'document_reference.total_sale' => 'nullable|numeric',
//            'document_reference.allowance_amount' => 'nullable|numeric',
//            'document_reference.tax_amount' => 'nullable|numeric',

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
