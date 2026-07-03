<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ConfigurationSoftwareRequest extends FormRequest
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
            'id' => 'nullable|string',
            'pin' => 'nullable|required_with:id|numeric|digits:5',
            'url' => 'nullable|string|url',
            'idpayroll' => 'nullable|string',
            'pinpayroll' => 'nullable|required_with:idpayroll|numeric|digits:5',
            'urlpayroll' => 'nullable|string|url',
//            'idsd' => 'nullable|string',
//            'pinsd' => 'nullable|required_with:idsd|numeric|digits:5',
//            'urlsd' => 'nullable|string|url',
            'ideqdocs' => 'nullable|string',
            'pineqdocs' => 'nullable|required_with:ideqdocs|numeric|digits:5',
            'urleqdocs' => 'nullable|string|url',
            'idsupportdocument' => 'nullable|string',
            'pinsupportdocument' => 'nullable|required_with:idsupportdocument|numeric|digits:5',
            'urlsupportdocument' => 'nullable|string|url',
        ];
    }
}
