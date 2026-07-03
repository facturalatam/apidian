<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ConfigurationEnvironmentRequest extends FormRequest
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
            // Campos originales (nombres técnicos)
            'type_environment_id' => 'nullable|exists:type_environments,id',
            'payroll_type_environment_id' => 'nullable|exists:type_environments,id',
            'eqdocs_type_environment_id' => 'nullable|exists:type_environments,id',
            'support_document_type_environment_id' => 'nullable|exists:type_environments,id',
            'event_type_environment_id' => 'nullable|exists:type_environments,id',
            
            // Campos descriptivos (alternativa más amigable)
            'factura_ventas' => 'nullable|exists:type_environments,id',
            'nomina' => 'nullable|exists:type_environments,id',
            'pos' => 'nullable|exists:type_environments,id',
            'documentos_soporte' => 'nullable|exists:type_environments,id',
            'eventos_radian' => 'nullable|exists:type_environments,id',
        ];
    }
        /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'type_environment_id.exists' => 'El ambiente de factura de ventas seleccionado no es válido.',
            'payroll_type_environment_id.exists' => 'El ambiente de nómina seleccionado no es válido.',
            'eqdocs_type_environment_id.exists' => 'El ambiente de POS seleccionado no es válido.',
            'support_document_type_environment_id.exists' => 'El ambiente de documentos soporte seleccionado no es válido.',
            'event_type_environment_id.exists' => 'El ambiente de eventos RADIAN seleccionado no es válido.',
            
            'factura_ventas.exists' => 'El ambiente de factura de ventas seleccionado no es válido.',
            'nomina.exists' => 'El ambiente de nómina seleccionado no es válido.',
            'pos.exists' => 'El ambiente de POS seleccionado no es válido.',
            'documentos_soporte.exists' => 'El ambiente de documentos soporte seleccionado no es válido.',
            'eventos_radian.exists' => 'El ambiente de eventos RADIAN seleccionado no es válido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'type_environment_id' => 'ambiente de factura de ventas',
            'payroll_type_environment_id' => 'ambiente de nómina',
            'eqdocs_type_environment_id' => 'ambiente de POS',
            'support_document_type_environment_id' => 'ambiente de documentos soporte',
            'event_type_environment_id' => 'ambiente de eventos RADIAN',
            
            'factura_ventas' => 'factura de ventas',
            'nomina' => 'nómina',
            'pos' => 'POS',
            'documentos_soporte' => 'documentos soporte',
            'eventos_radian' => 'eventos RADIAN',
        ];
    }
}
