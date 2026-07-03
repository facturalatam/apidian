<?php

namespace App\Http\Requests\Api;

use App\Rules\ResolutionSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class sdCreditNoteRequest extends FormRequest
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
        $this->count_resolutions = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->count();
        if($this->count_resolutions < 2)
            $this->resolution = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->first();
        else{
            $this->count_resolutions = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->where('resolution', $this->resolution_number)->count();
            if($this->count_resolutions < 2){
               $this->resolution = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->where('resolution', $this->resolution_number)->first();
            }
            else
                $this->resolution = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->where('resolution', $this->resolution_number)->where('prefix', $this->prefix)->first();
        }

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

            // Prefijo del Nombre del AttachedDocument
            'atacheddocument_name_prefix' => 'nullable|string',

            // Regimen ZESE
            'seze' => 'nullable|string',  // Cadena indicando año de inicio Regimen ZESE y año de formacion de sociedad separados por guion Ejemplo 2021-2017

            // Nota Encabezado y pie de pagina
            'foot_note' => 'nullable|string',
            'head_note' => 'nullable|string',

            // Desactivar texto de confirmacion de pago
            'disable_confirmation_text' => 'nullable|boolean',

            // Enviar Correo al Adquiriente
            'sendmail' => 'nullable|boolean',
            'sendmailtome' => 'nullable|boolean',
            'send_customer_credentials' => 'nullable|boolean',

            // Lista de correos a enviar copia
            'email_cc_list' => 'nullable|array',
            'email_cc_list.*.email' => 'nullable|required_with:email_cc_list,|string|email',

            // Documentos en base64 para adjuntar en el attacheddocument
            'annexes' => 'nullable|array',
            'annexes.*.document' => 'nullable|required_with:annexes|string',
            'annexes.*.extension' => 'nullable|required_with:annexes|string',

            // HTML string body email
            'html_header' => 'nullable|string',
            'html_body' => 'nullable|string',
            'html_buttons' => 'nullable|string',
            'html_footer' => 'nullable|string',

            // Nombre Archivo
            'GuardarEn' => 'nullable|string',

            // Document
            'type_document_id' => [
                'required',
                'in:13',
                'exists:type_documents,id',
                new ResolutionSetting(),
            ],

            // Date time
            'date' => 'nullable|date_format:Y-m-d',
            'time' => 'nullable|date_format:H:i:s',

            // Notes
            'notes' => 'nullable|string',

            // Tipo operacion
            'type_operation_id' => 'nullable|in:23,24',

            // Resolution number for document sending
            'resolution_number' => Rule::requiredIf(function(){
                if(auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->count() >= 2)
                  return true;
                else
                  return false;
            }),

            // Prefijo de la resolucion a utilizar

            'prefix' => Rule::requiredIf(function(){
                if(auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->where('resolution_number', $this->resolution_number)->count() >= 2)
                    return true;
                else
                    return false;
            }),

            // Consecutive
            'number' => 'required|integer|between:'.optional($this->resolution)->from.','.optional($this->resolution)->to,

            // Discrepancy Response
            'discrepancyresponsecode' => 'nullable|integer|between:1,6',
            'discrepancyresponsedescription' => 'nullable|string',

            // Billing Reference
            'billing_reference' => 'required|array',
            'billing_reference.number' => 'required|string',
            'billing_reference.uuid' => 'required|string|size:96',
            'billing_reference.issue_date' => 'required|date_format:Y-m-d',

            // Id moneda negociacion
            'idcurrency' => 'nullable|integer|exists:type_currencies,id',
            'calculationrate' => 'nullable|required_with:idcurrency|numeric',
            'calculationratedate' => 'nullable|required_with:idcurrency|date_format:Y-m-d',

            // Seller
            'seller' => 'required|array',
            'seller.identification_number' => 'required|alpha_num|between:1,15',
            'seller.dv' => 'nullable|numeric|digits:1|dian_dv:'.$this->seller["identification_number"],
            'seller.type_document_identification_id' => 'nullable|exists:type_document_identifications,id',
            'seller.type_organization_id' => 'nullable|exists:type_organizations,id',
            'seller.language_id' => 'nullable|exists:languages,id',
            'seller.country_id' => 'nullable|exists:countries,id',
            'seller.municipality_id' => 'nullable|exists:municipalities,id',
            'seller.municipality_id_fact' => 'nullable|exists:municipalities,codefacturador',
            'seller.type_regime_id' => 'nullable|exists:type_regimes,id',
            'seller.tax_id' => 'nullable|exists:taxes,id',
            'seller.type_liability_id' => 'nullable|exists:type_liabilities,id',
            'seller.name' => 'required|string',
            'seller.phone' => 'required|string|max:20',
            'seller.address' => 'required|string',
            'seller.email' => 'nullable|string|email',
            'seller.merchant_registration' => 'required|string',
            'seller.postal_zone_code' => 'required|numeric',

            // SMTP Server Parameters
            'smtp_parameters' => 'nullable|array',
            'smtp_parameters.host' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.port' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.username' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.password' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.encryption' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.from_address' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.from_name' => 'nullable|required_with:smtp_parameters|string',

            // Payment form
            'payment_form' => 'nullable|array',
            'payment_form.payment_form_id' => 'nullable|exists:payment_forms,id',
            'payment_form.payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_form.payment_due_date' => 'nullable|required_if:payment_form.payment_form_id,=,2|after_or_equal:date|date_format:Y-m-d',
            'payment_form.duration_measure' => 'nullable|required_if:payment_form.payment_form_id,=,2|numeric|digits_between:1,3',

            // Allowance charges
            'allowance_charges' => 'nullable|array',
            'allowance_charges.*.charge_indicator' => 'nullable|required_with:allowance_charges|boolean',
            'allowance_charges.*.discount_id' => 'nullable|required_if:allowance_charges.*.charge_indicator,false|exists:discounts,id',
            'allowance_charges.*.allowance_charge_reason' => 'nullable|required_with:allowance_charges|string',
            'allowance_charges.*.amount' => 'nullable|required_with:allowance_charges|numeric',
            'allowance_charges.*.base_amount' => 'nullable|required_with:allowance_charges|numeric',

            // Tax totals
            'tax_totals' => 'nullable|array',
            'tax_totals.*.tax_id' => 'nullable|required_with:allowance_charges|exists:taxes,id',
            'tax_totals.*.percent' => 'nullable|required_unless:tax_totals.*.tax_id,10|numeric',
            'tax_totals.*.tax_amount' => 'nullable|required_with:allowance_charges|numeric',
            'tax_totals.*.taxable_amount' => 'nullable|required_with:allowance_charges|numeric',
            'tax_totals.*.unit_measure_id' => 'nullable|required_if:tax_totals.*.tax_id,10|exists:unit_measures,id',
            'tax_totals.*.per_unit_amount' => 'nullable|required_if:tax_totals.*.tax_id,10|numeric',
            'tax_totals.*.base_unit_measure' => 'nullable|required_if:tax_totals.*.tax_id,10|numeric',

            // Legal monetary totals
            'legal_monetary_totals' => 'required|array',
            'legal_monetary_totals.line_extension_amount' => 'required|numeric',
            'legal_monetary_totals.tax_exclusive_amount' => 'required|numeric',
            'legal_monetary_totals.tax_inclusive_amount' => 'required|numeric',
            'legal_monetary_totals.allowance_total_amount' => 'nullable|numeric',
            'legal_monetary_totals.charge_total_amount' => 'nullable|numeric',
            'legal_monetary_totals.payable_amount' => 'required|numeric',

            // Credit note lines
            'credit_note_lines' => 'required|array',
            'credit_note_lines.*.unit_measure_id' => 'required|exists:unit_measures,id',
            'credit_note_lines.*.invoiced_quantity' => 'required|numeric',
            'credit_note_lines.*.line_extension_amount' => 'required|numeric',
            'credit_note_lines.*.free_of_charge_indicator' => 'required|boolean',
            'credit_note_lines.*.reference_price_id' => 'nullable|required_if:credit_note_lines.*.free_of_charge_indicator,true|exists:reference_prices,id',
            'credit_note_lines.*.allowance_charges' => 'nullable|array',
            'credit_note_lines.*.allowance_charges.*.charge_indicator' => 'nullable|required_with:credit_note_lines.*.allowance_charges|boolean',
            'credit_note_lines.*.allowance_charges.*.allowance_charge_reason' => 'nullable|required_with:credit_note_lines.*.allowance_charges|string',
            'credit_note_lines.*.allowance_charges.*.amount' => 'nullable|required_with:credit_note_lines.*.allowance_charges|numeric',
            'credit_note_lines.*.allowance_charges.*.base_amount' => 'nullable|required_if:credit_note_lines.*.allowance_charges.*.charge_indicator,false|numeric',
            'credit_note_lines.*.allowance_charges.*.multiplier_factor_numeric' => 'nullable|required_if:credit_note_lines.*.allowance_charges.*.charge_indicator,true|numeric',
            'credit_note_lines.*.tax_totals' => 'nullable|array',
            'credit_note_lines.*.tax_totals.*.tax_id' => 'nullable|required_with:credit_note_lines.*.tax_totals|exists:taxes,id',
            'credit_note_lines.*.tax_totals.*.tax_amount' => 'nullable|required_with:credit_note_lines.*.tax_totals|numeric',
            'credit_note_lines.*.tax_totals.*.taxable_amount' => 'nullable|required_with:credit_note_lines.*.tax_totals|numeric',
            'credit_note_lines.*.tax_totals.*.percent' => 'nullable|required_unless:credit_note_lines.*.tax_totals.*.tax_id,10|numeric',
            'credit_note_lines.*.tax_totals.*.unit_measure_id' => 'nullable|required_if:credit_note_lines.*.tax_totals.*.tax_id,10|exists:unit_measures,id',
            'credit_note_lines.*.tax_totals.*.per_unit_amount' => 'nullable|required_if:credit_note_lines.*.tax_totals.*.tax_id,10|numeric',
            'credit_note_lines.*.tax_totals.*.base_unit_measure' => 'nullable|required_if:credit_note_lines.*.tax_totals.*.tax_id,10|numeric',
            'credit_note_lines.*.description' => 'required|string',
            'credit_note_lines.*.notes' => 'nullable|string',
            'credit_note_lines.*.code' => 'required|string',
            'credit_note_lines.*.type_item_identification_id' => 'required|exists:type_item_identifications,id',
            'credit_note_lines.*.price_amount' => 'required|numeric',
            'credit_note_lines.*.base_quantity' => 'required|numeric',
        ];
    }
}
