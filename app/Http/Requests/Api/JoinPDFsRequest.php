<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JoinPDFsRequest extends FormRequest
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
            // Lista de correos a enviar copia
            'pdfs' => 'nullable|array',
            'pdfs.*.type_document_id' => 'required|in:1,2,3,4,5,9,10,11,12',
            'pdfs.*.prefix' => 'required|string',
            'pdfs.*.number' => 'required|integer',
            'name_joined_pdfs' => 'required|string',
        ];
    }
}
