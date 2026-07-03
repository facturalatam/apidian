<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SignDocumentRequest extends FormRequest
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
            'documentbase64' => 'required|string',
            'certificate' => 'required|string',
            'password' => 'required|string',
            'softwareid' => 'required|string',
            'technicalKey' => 'required_if:tipodoc,=,INVOICE|string',
            'pin' => 'required|integer',
            'documentnumber' => 'required|integer',
            'tipodoc' => 'required|string',
        ];
    }
}
