<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class XmlDocumentRequest extends FormRequest
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
            // Consulta de nomina
            'is_payroll' => 'nullable|boolean',
            'is_event' => 'nullable|boolean',
        ];
    }
}
