<?php

namespace App\Http\Requests\Api;

use App\Rules\ResolutionSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceExportRequest extends FormRequest
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

            // Documentos en base64 para adjuntar en el attacheddocument
            'annexes' => 'nullable|array',
            'annexes.*.document' => 'nullable|required_with:annexes|string',
            'annexes.*.extension' => 'nullable|required_with:annexes|string',

            // Prefijo del Nombre del AttachedDocument
            'atacheddocument_name_prefix' => 'nullable|string',

            // Regimen ZESE
            'seze' => 'nullable|string',  // Cadena indicando año de inicio Regimen ZESE y año de formacion de sociedad separados por guion Ejemplo 2021-2017

            // Nota Encabezado y pie de pagina
            'foot_note' => 'nullable|string',
            'head_note' => 'nullable|string',

            // Desactivar texto de confirmacion de pago
            'disable_confirmation_text' => 'nullable|boolean',

            // Nombre Archivo
            'GuardarEn' => 'nullable|string',

            // Enviar Correo al Adquiriente
            'sendmail' => 'nullable|boolean',
            'sendmailtome' => 'nullable|boolean',
            'send_customer_credentials' => 'nullable|boolean',

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

            // Lista de correos a enviar copia
            'email_cc_list' => 'nullable|array',
            'email_cc_list.*.email' => 'nullable|required_with:email_cc_list,|string|email',

            // Document
            'type_document_id' => [
                'required',
                'in:2',
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
            'number' => 'required|integer|between:'.optional($this->resolution)->from.','.optional($this->resolution)->to,

            // Date time
            'date' => 'nullable|date_format:Y-m-d|after_or_equal:'.optional($this->resolution)->date_from.'|before_or_equal:'.optional($this->resolution)->date_to,
            'time' => 'nullable|date_format:H:i:s',

            // Notes
            'notes' => 'nullable|string',

            // Id moneda negociacion
//            'idcurrency' => 'required|integer|exists:type_currencies,id',
//            'calculationrate' => 'required|numeric',
//            'calculationratedate' => 'nullable|date_format:Y-m-d',

            // Suplemento K
            'k_supplement' => 'nullable|array',
            'k_supplement.responsible_incharge' => 'nullable|string',
            'k_supplement.departure_place' => 'nullable|string',
            'k_supplement.conveyance' => 'nullable|string',
            'k_supplement.transport_document_type' => 'nullable|string',
            'k_supplement.transport_document_number' => 'nullable|string',
            'k_supplement.transporter_processor' => 'nullable|string',
            'k_supplement.merchandise_origin_country' => 'nullable|string',
            'k_supplement.destination' => 'nullable|string',
            'k_supplement.payment_means' => 'nullable|string',
            'k_supplement.insurance_carrier' => 'nullable|string',
            'k_supplement.observations' => 'nullable|string',
            'k_supplement.FctConvCop' => 'nullable|string',
            'k_supplement.MonedaCop' => 'nullable|string',
            'k_supplement.SubTotalCop' => 'nullable|string',
            'k_supplement.DescuentoDetalleCop' => 'nullable|string',
            'k_supplement.RecargoDetalleCop' => 'nullable|string',
            'k_supplement.TotalBrutoFacturaCop' => 'nullable|string',
            'k_supplement.TotIvaCop' => 'nullable|string',
            'k_supplement.TotIncCop' => 'nullable|string',
            'k_supplement.TotBolCop' => 'nullable|string',
            'k_supplement.ImpOtroCop' => 'nullable|string',
            'k_supplement.MntImpCop' => 'nullable|string',
            'k_supplement.TotalNetoFacturaCop' => 'nullable|string',
            'k_supplement.MntDctoCop' => 'nullable|string',
            'k_supplement.MntRcgoCop' => 'nullable|string',
            'k_supplement.VlrPagarCop' => 'nullable|string',
            'k_supplement.ReteFueCop' => 'nullable|string',
            'k_supplement.ReteIvaCop' => 'nullable|string',
            'k_supplement.ReteIcaCop' => 'nullable|string',
            'k_supplement.TotAnticiposCop' => 'nullable|string',

            // Tipo operacion
            'type_operation_id' => 'nullable|numeric|exists:type_operations',

            // Customer
            'customer' => 'required|array',
            'customer.identification_number' => 'required|alpha_num|between:1,15',
            'customer.dv' => 'nullable|numeric|digits:1|dian_dv:'.$this->customer["identification_number"],
            'customer.type_document_identification_id' => 'nullable|exists:type_document_identifications,id',
            'customer.type_organization_id' => 'nullable|exists:type_organizations,id',
            'customer.language_id' => 'nullable|exists:languages,id',
            'customer.country_id' => 'nullable|exists:countries,id',
            'customer.municipality_id' => 'nullable|exists:municipalities,id',
            'customer.municipality_id_fact' => 'nullable|exists:municipalities,codefacturador',
            'customer.municipality_name' => 'nullable|string',
            'customer.state_name' => 'nullable|string',
            'customer.type_regime_id' => 'nullable|exists:type_regimes,id',
            'customer.tax_id' => 'nullable|exists:taxes,id',
            'customer.type_liability_id' => 'nullable|exists:type_liabilities,id',
            'customer.type_liability_ids' => 'nullable|array|min:1',
            'customer.type_liability_ids.*' => 'integer|exists:type_liabilities,id',
            'customer.name' => 'required|string',
            'customer.phone' => 'required|string|max:20',
            'customer.address' => 'required|string',
            'customer.email' => 'required|string|email',
            'customer.merchant_registration' => 'required|string',

            // SMTP Server Parameters
            'smtp_parameters' => 'nullable|array',
            'smtp_parameters.host' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.port' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.username' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.password' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.encryption' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.from_address' => 'nullable|required_with:smtp_parameters|string',
            'smtp_parameters.from_name' => 'nullable|required_with:smtp_parameters|string',

            // Order Reference
            'order_reference' => 'nullable|array',
            'order_reference.id_order' => 'nullable|string',
            'order_reference.issue_date_order' => 'nullable|date_format:Y-m-d',

            // Receipt Document References
            'receipt_document_references' => 'nullable|array',
            'receipt_document_references.*.id' => 'required_with:receipt_document_references|string|max:20',
            'receipt_document_references.*.issue_date' => 'nullable|date_format:Y-m-d',

            // Additional Document Reference
            'additional_document_reference' => 'nullable|array',
            'additional_document_reference.id' => 'nullable|string',
            'additional_document_reference.date' => 'nullable|date_format:Y-m-d',
            'additional_document_reference.type_document_id' => 'nullable|exists:type_documents,id',

            // Delivery Terms
            'deliveryterms' => 'required|array',
            'deliveryterms.special_terms' => 'required|string',
            'deliveryterms.loss_risk_responsibility_code' => 'required|string|in:CFR,CIP,CPT,DAP,DAT,DDP,FOB,EXW,CIF,FAS,FCA',
            'deliveryterms.loss_risk' => 'required|string',

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

            // Legal monetary totals
            'legal_monetary_totals' => 'required|array',
            'legal_monetary_totals.line_extension_amount' => 'required|numeric',
            'legal_monetary_totals.tax_exclusive_amount' => 'required|numeric',
            'legal_monetary_totals.tax_inclusive_amount' => 'required|numeric',
            'legal_monetary_totals.allowance_total_amount' => 'nullable|numeric',
            'legal_monetary_totals.charge_total_amount' => 'nullable|numeric',
            'legal_monetary_totals.payable_amount' => 'required|numeric',

            // Invoice lines
            'invoice_lines' => 'required|array',
            'invoice_lines.*.unit_measure_id' => 'required|exists:unit_measures,id',
            'invoice_lines.*.invoiced_quantity' => 'required|numeric',
            'invoice_lines.*.line_extension_amount' => 'required|numeric',
            'invoice_lines.*.free_of_charge_indicator' => 'required|boolean',
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
            'invoice_lines.*.description' => 'required|string',
            'invoice_lines.*.notes' => 'nullable|string',
            'invoice_lines.*.brandname' => 'nullable|string',
            'invoice_lines.*.modelname' => 'nullable|string',
            'invoice_lines.*.code' => 'required|string',
            'invoice_lines.*.type_item_identification_id' => 'required|exists:type_item_identifications,id',
            'invoice_lines.*.price_amount' => 'required|numeric',
            'invoice_lines.*.base_quantity' => 'required|numeric',
        ];
    }
}
