<?php

namespace App\Http\Requests\Api;

use App\Rules\ResolutionSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegeneratePDFRequest extends FormRequest
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
            if($this->count_resolutions < 2)
                $this->resolution = auth()->user()->company->resolutions->where('type_document_id', $this->type_document_id)->where('resolution', $this->resolution_number)->first();
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

            // Invoice template name
            'invoice_template' => 'nullable|string',

            // Dynamic field
            'dynamic_field' => 'nullable|array',
            'dynamic_field.name' => 'nullable|required_with:dynamic_field|string',
            'dynamic_field.value' => 'nullable|required_with:dynamic_field|string',
            'dynamic_field.add_to_total' => 'nullable|required_with:dynamic_field|boolean',

            // Other fields for templates
            'sales_assistant' => 'nullable|string',
            'web_site' => 'nullable|string',
            'template_token' => 'nullable|required_with:invoice_template|string',

            // Prefijo del Nombre del AttachedDocument
            'atacheddocument_name_prefix' => 'nullable|string',

            // CUFE
            'cufe' => 'nullable|string',

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

            // Nombre Archivo
            'GuardarEn' => 'nullable|string',

            // Document
            'type_document_id' => [
                'required',
                'in:1,2,3,4,5,9,10,11,12,13',
                'exists:type_documents,id',
                new ResolutionSetting(),
            ],

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
            'number' => 'nullable|integer|between:'.optional($this->resolution)->from.','.optional($this->resolution)->to,

            // Discrepancy Response
            'discrepancyresponsecode' => 'nullable|integer|between:1,6',
            'discrepancyresponsedescription' => 'nullable|string',

            // Discrepancy Response
            'AdditionalDocumentReferenceID' => 'nullable|string',
            'AdditionalDocumentReferenceDate' => 'nullable|date_format:Y-m-d',
            'AdditionalDocumentReferenceTypeDocument' => 'nullable|string|exists:type_documents,code',

            // Date time
            'date' => 'nullable|date_format:Y-m-d',
            'time' => 'nullable|date_format:H:i:s',

            // Notes
            'notes' => 'nullable|string',

            // Objeto contrato AIU
            'noteAIU' => 'nullable|string',

            //Elaborado y Revisado
            'elaborated' => 'nullable|string',
            'reviewed' => 'nullable|string',

            // Tipo operacion
            'type_operation_id' => 'nullable|numeric|exists:type_operations',

            // Billing Reference
            'billing_reference' => 'nullable|array',
            'billing_reference.number' => 'nullable|required_with:billing_reference|string',
            'billing_reference.uuid' => 'nullable|required_with:billing_reference|string|size:96',
            'billing_reference.issue_date' => 'nullable|required_with:billing_reference|date_format:Y-m-d',

            // Id moneda negociacion
            'idcurrency' => 'nullable|integer|exists:type_currencies,id',
            'calculationrate' => 'nullable|required_with:idcurrency|numeric',
            'calculationratedate' => 'nullable|required_with:idcurrency|date_format:Y-m-d',

            // Customer
            'customer' => 'nullable|array',
            'customer.identification_number' => 'nullable|alpha_num|between:1,15',
//            'customer.dv' => 'nullable|numeric|digits:1|dian_dv:'.$this->customer["identification_number"],
            'customer.type_document_identification_id' => 'nullable|exists:type_document_identifications,id',
            'customer.type_organization_id' => 'nullable|exists:type_organizations,id',
            'customer.language_id' => 'nullable|exists:languages,id',
            'customer.country_id' => 'nullable|exists:countries,id',
            'customer.municipality_id' => 'nullable|exists:municipalities,id',
            'customer.municipality_id_fact' => 'nullable|exists:municipalities,codefacturador',
            'customer.type_regime_id' => 'nullable|exists:type_regimes,id',
            'customer.tax_id' => 'nullable|exists:taxes,id',
            'customer.type_liability_id' => 'nullable|exists:type_liabilities,id',
            'customer.type_liability_ids' => 'nullable|array|min:1',
            'customer.type_liability_ids.*' => 'integer|exists:type_liabilities,id',
            'customer.name' => 'nullable|string',
            'customer.phone' => 'nullable|string|max:20',
            'customer.address' => 'nullable|string',
            'customer.email' => 'nullable|string|email',
            'customer.merchant_registration' => 'nullable|string',

            // Seller
            'seller' => 'nullable|array',
            'seller.identification_number' => 'nullable|alpha_num|between:1,15',
//            'seller.dv' => 'nullable|numeric|digits:1|dian_dv:'.$this->seller["identification_number"],
            'seller.type_document_identification_id' => 'nullable|exists:type_document_identifications,id',
            'seller.type_organization_id' => 'nullable|exists:type_organizations,id',
            'seller.language_id' => 'nullable|exists:languages,id',
            'seller.country_id' => 'nullable|exists:countries,id',
            'seller.municipality_id' => 'nullable|exists:municipalities,id',
            'seller.municipality_id_fact' => 'nullable|exists:municipalities,codefacturador',
            'seller.type_regime_id' => 'nullable|exists:type_regimes,id',
            'seller.tax_id' => 'nullable|exists:taxes,id',
            'seller.type_liability_id' => 'nullable|exists:type_liabilities,id',
            'seller.name' => 'nullable|string',
            'seller.phone' => 'nullable|string|max:20',
            'seller.address' => 'nullable|string',
            'seller.email' => 'nullable|string|email',
            'seller.merchant_registration' => 'nullable|string',
            'seller.postal_zone_code' => 'nullable|numeric',

            // SMTP Server Parameters
            'smtp_parameters' => 'nullable|array',
            'smtp_parameters.host' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.port' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.username' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.password' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.encryption' => 'nullable|required_with:smtp_parameters|string',

            // Order Reference
            'order_reference' => 'nullable|array',
            'order_reference.id_order' => 'nullable|string',
            'order_reference.issue_date_order' => 'nullable|date_format:Y-m-d',

            // Delivery
            'delivery' => 'nullable|array',
            'delivery.language_id' => 'nullable|exists:languages,id',
            'delivery.country_id' => 'nullable|exists:countries,id',
            'delivery.municipality_id' => 'nullable|exists:municipalities,id',
            'delivery.address' => 'nullable|required_with:delivery|string',
            'delivery.actual_delivery_date' => 'nullable|required_with:delivery|date_format:Y-m-d',

            // Delivery Party
            'deliveryparty' => 'nullable|required_with:delivery|array',
            'deliveryparty.identification_number' => 'nullable|required_with:deliveryparty|numeric|digits_between:1,15',
//            'deliveryparty.dv' => 'nullable|required_with:delivery|numeric|digits:1|dian_dv:'.$this->deliveryparty["identification_number"],
            'deliveryparty.type_document_identification_id' => 'nullable|exists:type_document_identifications,id',
            'deliveryparty.type_organization_id' => 'nullable|exists:type_organizations,id',
            'deliveryparty.language_id' => 'nullable|exists:languages,id',
            'deliveryparty.country_id' => 'nullable|exists:countries,id',
            'deliveryparty.municipality_id' => 'nullable|exists:municipalities,id',
            'deliveryparty.type_regime_id' => 'nullable|exists:type_regimes,id',
            'deliveryparty.tax_id' => 'nullable|exists:taxes,id',
            'deliveryparty.type_liability_id' => 'nullable|exists:type_liabilities,id',
            'deliveryparty.name' => 'nullable|required_with:deliveryparty|string',
            'deliveryparty.phone' => 'nullable|required_with:deliveryparty|string|max:20',
            'deliveryparty.address' => 'nullable|required_with:deliveryparty|string',
            'deliveryparty.email' => 'nullable|required_with:deliveryparty|string|email',
            'deliveryparty.merchant_registration' => 'nullable|string',

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

            // Holding Tax totals
            'with_holding_tax_total' => 'nullable|array',
            'with_holding_tax_total.*.tax_id' => 'nullable|exists:taxes,id|numeric',
            'with_holding_tax_total.*.percent' => 'nullable|numeric',
            'with_holding_tax_total.*.tax_amount' => 'nullable|numeric',
            'with_holding_tax_total.*.taxable_amount' => 'nullable|numeric',
            'with_holding_tax_total.*.unit_measure_id' => 'nullable|exists:unit_measures,id',
            'with_holding_tax_total.*.per_unit_amount' => 'nullable|numeric',
            'with_holding_tax_total.*.base_unit_measure' => 'nullable|numeric',

            // Prepaid Payment
            'prepaid_payment' => 'nullable|array',
            'prepaid_payment.idpayment' => 'nullable|string',
            'prepaid_payment.paidamount' => 'nullable|numeric',
            'prepaid_payment.receiveddate' => 'nullable|date_format:Y-m-d',
            'prepaid_payment.paiddate' => 'nullable|date_format:Y-m-d',
            'prepaid_payment.instructionid' => 'nullable|string',
            // Prepaid Payments
            'prepaid_payments' => 'nullable|array',
            'prepaid_payments.*.idpayment' => 'nullable|string',
            'prepaid_payments.*.paidamount' => 'nullable|numeric',
            'prepaid_payments.*.receiveddate' => 'nullable|date_format:Y-m-d',
            'prepaid_payments.*.paiddate' => 'nullable|date_format:Y-m-d',
            'prepaid_payments.*.instructionid' => 'nullable|string',

            // Previous Balance
            'previous_balance' => 'nullable|numeric',

            // Legal monetary totals
            'legal_monetary_totals' => 'nullable|array',
            'legal_monetary_totals.line_extension_amount' => 'nullable|numeric',
            'legal_monetary_totals.tax_exclusive_amount' => 'nullable|numeric',
            'legal_monetary_totals.tax_inclusive_amount' => 'nullable|numeric',
            'legal_monetary_totals.allowance_total_amount' => 'nullable|numeric',
            'legal_monetary_totals.charge_total_amount' => 'nullable|numeric',
            'legal_monetary_totals.pre_paid_amount' => 'nullable|required_with:prepaid_payment|numeric',
            'legal_monetary_totals.payable_amount' => 'nullable|numeric',

            // Requested monetary totals
            'requested_monetary_totals' => 'nullable|array',
            'requested_monetary_totals.line_extension_amount' => 'nullable|numeric',
            'requested_monetary_totals.tax_exclusive_amount' => 'nullable|numeric',
            'requested_monetary_totals.tax_inclusive_amount' => 'nullable|numeric',
            'requested_monetary_totals.allowance_total_amount' => 'nullable|numeric',
            'requested_monetary_totals.charge_total_amount' => 'nullable|numeric',
            'requested_monetary_totals.payable_amount' => 'nullable|numeric',

            // Invoice lines
            'invoice_lines' => 'nullable|array',
            'invoice_lines.*.unit_measure_id' => 'nullable|exists:unit_measures,id',
            'invoice_lines.*.invoiced_quantity' => 'nullable|numeric',
            'invoice_lines.*.line_extension_amount' => 'nullable|numeric',
            'invoice_lines.*.free_of_charge_indicator' => 'nullable|boolean',
            'invoice_lines.*.reference_price_id' => 'nullable|required_if:invoice_lines.*.free_of_charge_indicator,true|exists:reference_prices,id',
            'invoice_lines.*.allowance_charges' => 'nullable|array',
            'invoice_lines.*.allowance_charges.*.charge_indicator' => 'nullable|required_with:invoice_lines.*.allowance_charges|boolean',
            'invoice_lines.*.allowance_charges.*.allowance_charge_reason' => 'nullable|required_with:invoice_lines.*.allowance_charges|string',
            'invoice_lines.*.allowance_charges.*.amount' => 'nullable|required_with:invoice_lines.*.allowance_charges|numeric',
            'invoice_lines.*.allowance_charges.*.base_amount' => 'nullable|required_if:invoice_lines.*.allowance_charges.*.charge_indicator,false|numeric',
            'invoice_lines.*.allowance_charges.*.multiplier_factor_numeric' => 'nullable|required_if:invoice_lines.*.allowance_charges.*.charge_indicator,true|numeric',
            'invoice_lines.*.tax_totals' => 'nullable|array',
            'invoice_lines.*.tax_totals.*.tax_id' => 'nullable|required_with:invoice_lines.*.tax_totals|exists:taxes,id',
            'invoice_lines.*.tax_totals.*.tax_amount' => 'nullable|required_with:invoice_lines.*.tax_totals|numeric',
            'invoice_lines.*.tax_totals.*.taxable_amount' => 'nullable|required_with:invoice_lines.*.tax_totals|numeric',
            'invoice_lines.*.tax_totals.*.percent' => 'nullable|required_unless:invoice_lines.*.tax_totals.*.tax_id,10|numeric',
            'invoice_lines.*.tax_totals.*.unit_measure_id' => 'nullable|required_if:invoice_lines.*.tax_totals.*.tax_id,10|exists:unit_measures,id',
            'invoice_lines.*.tax_totals.*.per_unit_amount' => 'nullable|required_if:invoice_lines.*.tax_totals.*.tax_id,10|numeric',
            'invoice_lines.*.tax_totals.*.base_unit_measure' => 'nullable|required_if:invoice_lines.*.tax_totals.*.tax_id,10|numeric',
            'invoice_lines.*.description' => 'nullable|string',
            'invoice_lines.*.notes' => 'nullable|string',
            'invoice_lines.*.agentparty' => 'nullable|numeric|digits_between:1,15',
            'invoice_lines.*.agentparty_dv' => 'nullable|numeric|digits:1',
            'invoice_lines.*.code' => 'nullable|string',
            'invoice_lines.*.type_item_identification_id' => 'nullable|exists:type_item_identifications,id',
            'invoice_lines.*.price_amount' => 'nullable|numeric',
            'invoice_lines.*.base_quantity' => 'nullable|numeric',
            'invoice_lines.*.type_generation_transmition_id' => 'nullable|exists:type_generation_transmitions,id',
            'invoice_lines.*.start_date' => 'nullable|date_format:Y-m-d',

            // Credit note lines
            'credit_note_lines' => 'nullable|array',
            'credit_note_lines.*.unit_measure_id' => 'nullable|exists:unit_measures,id',
            'credit_note_lines.*.invoiced_quantity' => 'nullable|numeric',
            'credit_note_lines.*.line_extension_amount' => 'nullable|numeric',
            'credit_note_lines.*.free_of_charge_indicator' => 'nullable|boolean',
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
            'credit_note_lines.*.description' => 'nullable|string',
            'credit_note_lines.*.notes' => 'nullable|string',
            'credit_note_lines.*.code' => 'nullable|string',
            'credit_note_lines.*.type_item_identification_id' => 'nullable|exists:type_item_identifications,id',
            'credit_note_lines.*.price_amount' => 'nullable|numeric',
            'credit_note_lines.*.base_quantity' => 'nullable|numeric',

            // Debit note lines
            'debit_note_lines' => 'nullable|array',
            'debit_note_lines.*.unit_measure_id' => 'nullable|exists:unit_measures,id',
            'debit_note_lines.*.invoiced_quantity' => 'nullable|numeric',
            'debit_note_lines.*.line_extension_amount' => 'nullable|numeric',
            'debit_note_lines.*.free_of_charge_indicator' => 'nullable|boolean',
            'debit_note_lines.*.reference_price_id' => 'nullable|required_if:debit_note_lines.*.free_of_charge_indicator,true|exists:reference_prices,id',
            'debit_note_lines.*.allowance_charges' => 'nullable|array',
            'debit_note_lines.*.allowance_charges.*.charge_indicator' => 'nullable|required_with:debit_note_lines.*.allowance_charges|boolean',
            'debit_note_lines.*.allowance_charges.*.allowance_charge_reason' => 'nullable|required_with:debit_note_lines.*.allowance_charges|string',
            'debit_note_lines.*.allowance_charges.*.amount' => 'nullable|required_with:debit_note_lines.*.allowance_charges|numeric',
            'debit_note_lines.*.allowance_charges.*.base_amount' => 'nullable|required_if:debit_note_lines.*.allowance_charges.*.charge_indicator,false|numeric',
            'debit_note_lines.*.allowance_charges.*.multiplier_factor_numeric' => 'nullable|required_if:debit_note_lines.*.allowance_charges.*.charge_indicator,true|numeric',
            'debit_note_lines.*.tax_totals' => 'nullable|array',
            'debit_note_lines.*.tax_totals.*.tax_id' => 'nullable|required_with:debit_note_lines.*.tax_totals|exists:taxes,id',
            'debit_note_lines.*.tax_totals.*.tax_amount' => 'nullable|required_with:debit_note_lines.*.tax_totals|numeric',
            'debit_note_lines.*.tax_totals.*.taxable_amount' => 'nullable|required_with:debit_note_lines.*.tax_totals|numeric',
            'debit_note_lines.*.tax_totals.*.percent' => 'nullable|required_unless:debit_note_lines.*.tax_totals.*.tax_id,10|numeric',
            'debit_note_lines.*.tax_totals.*.unit_measure_id' => 'nullable|required_if:debit_note_lines.*.tax_totals.*.tax_id,10|exists:unit_measures,id',
            'debit_note_lines.*.tax_totals.*.per_unit_amount' => 'nullable|required_if:debit_note_lines.*.tax_totals.*.tax_id,10|numeric',
            'debit_note_lines.*.tax_totals.*.base_unit_measure' => 'nullable|required_if:debit_note_lines.*.tax_totals.*.tax_id,10|numeric',
            'debit_note_lines.*.description' => 'nullable|string',
            'debit_note_lines.*.notes' => 'nullable|string',
            'debit_note_lines.*.code' => 'nullable|string',
            'debit_note_lines.*.type_item_identification_id' => 'nullable|exists:type_item_identifications,id',
            'debit_note_lines.*.price_amount' => 'nullable|numeric',
            'debit_note_lines.*.base_quantity' => 'nullable|numeric',

            // Payroll Fields

            // Novelty

            'novelty' => 'nullable|array',
            'novelty.novelty' => 'nullable|required_with:novelty|boolean',
            'novelty.uuidnov' => 'nullable|required_if:novelty.novelty,true|string',

            // Replace Predecessor
            'predecessor' => 'nullable|array',
            'predecessor.predecessor_number' => 'nullable|integer',
            'predecessor.predecessor_cune' => 'nullable|string',
            'predecessor.predecessor_issue_date' => 'nullable|date_format:Y-m-d',

            // Period
            'period' => 'nullable|array',
            'period.admision_date' => 'nullable|date_format:Y-m-d',
            'period.retirement_date' => 'nullable|date_format:Y-m-d',
            'period.settlement_start_date' => 'nullable|date_format:Y-m-d',
            'period.settlement_end_date' => 'nullable|date_format:Y-m-d',
            'period.worked_time' => 'nullable|numeric',
            'period.issue_date' => 'nullable|date_format:Y-m-d',

            // Secuence number
            'worker_code' => 'nullable|string',

            'consecutive' => 'nullable|integer|between:'.optional($this->resolution)->from.','.optional($this->resolution)->to,

            // General Information
            'payroll_period_id' => 'nullable|exists:payroll_periods,id',
            'notes' => 'nullable|string',

            // Worker
            'worker' => 'nullable|array',
            'worker.type_worker_id' => 'nullable|exists:type_workers,id',
            'worker.sub_type_worker_id' => 'nullable|exists:sub_type_workers,id',
            'worker.payroll_type_document_identification_id' => 'nullable|exists:payroll_type_document_identifications,id',
            'worker.municipality_id' => 'nullable|exists:municipalities,id',
            'worker.type_contract_id' => 'nullable|exists:type_contracts,id',
            'worker.high_risk_pension' => 'nullable|boolean',
            'worker.identification_number' => 'nullable|alpha_num|between:1,15',
            'worker.surname' => 'nullable|string',
            'worker.second_surname' => 'nullable|string',
            'worker.first_name' => 'nullable|string',
            'worker.middle_name' => 'nullable|string',
            'worker.address' => 'nullable|string',
            'worker.email' => 'nullable|string|email',
            'worker.integral_salarary' => 'nullable|boolean',
            'worker.salary' => 'nullable|numeric',
            'worker.worker_code' => 'nullable|string',

            // Payment
            'payment' => 'nullable|array',
            'payment.payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment.bank_name' => 'nullable|required_if:payment.payment_method_id,2,3,4,5,6,7,21,22,30,31,42,45,46,47,|string',
            'payment.account_type' => 'nullable|required_if:payment.payment_method_id,2,3,4,5,6,7,21,22,30,31,42,45,46,47,|string',
            'payment.account_number' => 'nullable|required_if:payment.payment_method_id,2,3,4,5,6,7,21,22,30,31,42,45,46,47,|string',

            // Payment Dates
            'payment_dates' => 'nullable|array',
            'payment_dates.*.payment_date' => 'nullable|date_format:Y-m-d',

            // Accrued
            'accrued' => 'nullable|array',
            'accrued.worked_days' => 'nullable|numeric|digits_between:1,2',
            'accrued.salary' => 'nullable|numeric',
            'accrued.transportation_allowance' => 'nullable|numeric',
            'accrued.salary_viatics' => 'nullable|numeric',
            'accrued.non_salary_viatics' => 'nullable|numeric',

            'accrued.*.HEDs' => 'nullable|array',
            'accrued.*.HEDs.*.start_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HEDs.*.end_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HEDs.*.quantity' => 'nullable|numeric',
            'accrued.*.HEDs.*.percentage' => 'nullable|exists:type_overtime_surcharge,id',
            'accrued.*.HEDs.*.payment' => 'nullable|numeric',

            'accrued.*.HENs' => 'nullable|array',
            'accrued.*.HENs.*.start_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HENs.*.end_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HENs.*.quantity' => 'nullable|numeric',
            'accrued.*.HENs.*.percentage' => 'nullable|exists:type_overtime_surcharge,id',
            'accrued.*.HENs.*.payment' => 'nullable|numeric',

            'accrued.*.HRNs' => 'nullable|array',
            'accrued.*.HRNs.*.start_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRNs.*.end_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRNs.*.quantity' => 'nullable|numeric',
            'accrued.*.HRNs.*.percentage' => 'nullable|exists:type_overtime_surcharge,id',
            'accrued.*.HRNs.*.payment' => 'nullable|numeric',

            'accrued.*.HEDDFs' => 'nullable|array',
            'accrued.*.HEDDFs.*.start_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HEDDFs.*.end_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HEDDFs.*.quantity' => 'nullable|numeric',
            'accrued.*.HEDDFs.*.percentage' => 'nullable|exists:type_overtime_surcharge,id',
            'accrued.*.HEDDFs.*.payment' => 'nullable|numeric',

            'accrued.*.HRDDFs' => 'nullable|array',
            'accrued.*.HRDDFs.*.start_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRDDFs.*.end_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRDDFs.*.quantity' => 'nullable|numeric',
            'accrued.*.HRDDFs.*.percentage' => 'nullable|exists:type_overtime_surcharge,id',
            'accrued.*.HRDDFs.*.payment' => 'nullable|numeric',

            'accrued.*.HENDFs' => 'nullable|array',
            'accrued.*.HENDFs.*.start_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HENDFs.*.end_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HENDFs.*.quantity' => 'nullable|numeric',
            'accrued.*.HENDFs.*.percentage' => 'nullable|exists:type_overtime_surcharge,id',
            'accrued.*.HENDFs.*.payment' => 'nullable|numeric',

            'accrued.*.HRNDFs' => 'nullable|array',
            'accrued.*.HRNDFs.*.start_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRNDFs.*.end_time' => 'nullable|date_format:Y-m-d\TH:i:s',
            'accrued.*.HRNDFs.*.quantity' => 'nullable|numeric',
            'accrued.*.HRNDFs.*.percentage' => 'nullable|exists:type_overtime_surcharge,id',
            'accrued.*.HRNDFs.*.payment' => 'nullable|numeric',

            'accrued.*.common_vacation' => 'nullable|array',
            'accrued.*.common_vacation.*.start_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.common_vacation.*.end_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.common_vacation.*.quantity' => 'nullable|numeric',
            'accrued.*.common_vacation.*.payment' => 'nullable|numeric',

            'accrued.*.paid_vacation' => 'nullable|array',
            'accrued.*.paid_vacation.*.quantity' => 'nullable|numeric',
            'accrued.*.paid_vacation.*.payment' => 'nullable|numeric',

            'accrued.*.service_bonus' => 'nullable|array',
            'accrued.*.service_bonus.*.quantity' => 'nullable|numeric',
            'accrued.*.service_bonus.*.payment' => 'nullable|numeric',
            'accrued.*.service_bonus.*.paymentNS' => 'nullable|numeric',

            'accrued.*.severance' => 'nullable|array',
            'accrued.*.severance.*.payment' => 'nullable|numeric',
            'accrued.*.severance.*.percentage' => 'nullable|numeric',
            'accrued.*.severance.*.interest_payment' => 'nullable|numeric',

            'accrued.*.work_disabilities' => 'nullable|array',
            'accrued.*.work_disabilities.*.start_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.work_disabilities.*.end_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.work_disabilities.*.quantity' => 'nullable|numeric',
            'accrued.*.work_disabilities.*.type' => 'nullable|exists:type_disabilities,id',
            'accrued.*.work_disabilities.*.payment' => 'nullable|numeric',

            'accrued.*.maternity_leave' => 'nullable|array',
            'accrued.*.maternity_leave.*.start_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.maternity_leave.*.end_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.maternity_leave.*.quantity' => 'nullable|numeric',
            'accrued.*.maternity_leave.*.payment' => 'nullable|numeric',

            'accrued.*.paid_leave' => 'nullable|array',
            'accrued.*.paid_leave.*.start_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.paid_leave.*.end_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.paid_leave.*.quantity' => 'nullable|numeric',
            'accrued.*.paid_leave.*.payment' => 'nullable|numeric',

            'accrued.*.non_paid_leave' => 'nullable|array',
            'accrued.*.non_paid_leave.*.start_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.non_paid_leave.*.end_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.non_paid_leave.*.quantity' => 'nullable|numeric',

            'accrued.*.bonuses' => 'nullable|array',
            'accrued.*.bonuses.*.salary_bonus' => 'nullable|numeric',
            'accrued.*.bonuses.*.non_salary_bonus' => 'nullable|numeric',

            'accrued.*.aid' => 'nullable|array',
            'accrued.*.aid.*.salary_assistance' => 'nullable|numeric',
            'accrued.*.aid.*.non_salary_assistance' => 'nullable|numeric',

            'accrued.*.legal_strike' => 'nullable|array',
            'accrued.*.legal_strike.*.start_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.legal_strike.*.end_date' => 'nullable|date_format:Y-m-d',
            'accrued.*.legal_strike.*.quantity' => 'nullable|numeric',

            'accrued.*.other_concepts' => 'nullable|array',
            'accrued.*.other_concepts.*.salary_concept' => 'nullable|numeric',
            'accrued.*.other_concepts.*.non_salary_concept' => 'nullable|numeric',
            'accrued.*.other_concepts.*.description_concept' => 'renullableuired|string',

            'accrued.*.compensations' => 'nullable|array',
            'accrued.*.compensations.*.ordinary_compensation' => 'nullable|numeric',
            'accrued.*.compensations.*.extraordinary_compensation' => 'nullable|numeric',

            'accrued.*.epctv_bonuses' => 'nullable|array',
            'accrued.*.epctv_bonuses.*.paymentS' => 'nullable|numeric',
            'accrued.*.epctv_bonuses.*.paymentNS' => 'nullable|numeric',
            'accrued.*.epctv_bonuses.*.salary_food_payment' => 'nullable|numeric',
            'accrued.*.epctv_bonuses.*.non_salary_food_payment' => 'nullable|numeric',

            'accrued.*.commissions' => 'nullable|array',
            'accrued.*.commissions.*.commission' => 'nullable|numeric',

            'accrued.*.third_party_payments' => 'nullable|array',
            'accrued.*.third_party_payments.*.third_party_payment' => 'nullable|numeric',

            'accrued.*.advances' => 'nullable|array',
            'accrued.*.advances.*.advance' => 'nullable|numeric',

            'accrued.endowment' => 'nullable|numeric',
            'accrued.sustenance_support' => 'nullable|numeric',
            'accrued.telecommuting' => 'nullable|numeric',
            'accrued.withdrawal_bonus' => 'nullable|numeric',
            'accrued.compensation' => 'nullable|numeric',
            'accrued.refund' => 'nullable|numeric',

            'accrued.accrued_total' => 'nullable|numeric',

            // Deductions
            'deductions' => 'nullable|array',
            'deductions.eps_type_law_deductions_id' => 'nullable|exists:type_law_deductions,id',
            'deductions.eps_deduction' => 'nullable|numeric',
            'deductions.pension_type_law_deductions_id' => 'nullable|exists:type_law_deductions,id',
            'deductions.pension_deduction' => 'nullable|numeric',
            'deductions.fondossp_type_law_deductions_id' => 'nullable|exists:type_law_deductions,id',
            'deductions.fondosp_deduction_SP' => 'nullable|numeric',
            'deductions.fondossp_sub_type_law_deductions_id' => 'nullable|exists:type_law_deductions,id',
            'deductions.fondosp_deduction_sub' => 'nullable|numeric',

            'deductions.*.labor_union' => 'nullable|array',
            'deductions.*.labor_union.*.percentage' => 'nullable|numeric',
            'deductions.*.labor_union.*.deduction' => 'nullable|numeric',

            'deductions.*.sanctions' => 'nullable|array',
            'deductions.*.sanctions.*.public_sanction' => 'nullable|numeric',
            'deductions.*.sanctions.*.private_sanction' => 'nullable|numeric',

            'deductions.*.orders' => 'nullable|array',
            'deductions.*.orders.*.description' => 'nullable|numeric',
            'deductions.*.orders.*.deduction' => 'nullable|numeric',

            'deductions.*.third_party_payments' => 'nullable|array',
            'deductions.*.third_party_payments.*.third_party_payment' => 'nullable|numeric',

            'deductions.*.advances' => 'nullable|array',
            'deductions.*.advances.*.advance' => 'nullable|numeric',

            'deductions.*.other_deductions' => 'nullable|array',
            'deductions.*.other_deductions.*.other_deduction' => 'nullable|numeric',

            'deductions.voluntary_pension' => 'nullable|numeric',
            'deductions.withholding_at_source' => 'nullable|numeric',
            'deductions.afc' => 'nullable|numeric',
            'deductions.cooperative' => 'nullable|numeric',
            'deductions.tax_liens' => 'nullable|numeric',
            'deductions.supplementary_plan' => 'nullable|numeric',
            'deductions.education' => 'nullable|numeric',
            'deductions.refund' => 'nullable|numeric',
            'deductions.debt' => 'nullable|numeric',

            'deductions.deductions_total' => 'nullable|numeric',
        ];
    }
}
