<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ConfigurationInitialDocumentRequest extends FormRequest
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
            'identification_number' => 'required|numeric|digits_between:1,15',
            'type_document_id' => 'required|exists:type_documents,id',
            'prefix' => 'nullable|string|max:4',
            'number' => 'required|integer',
        ];
    }
}
