<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AdministratorRequest extends FormRequest
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
            'dv' => 'required|numeric|digits:1',
            'address' => 'nullable|string',
            'phone' => 'nullable|numeric|digits_between:7,10',
            'email' => 'nullable|string|email|unique:administrators,email',
            'contact_name' => 'nullable|string',
            'password' => 'nullable|string',
            'plan' => 'required|string',
            'state' => 'nullable|boolean',
            'observation' => 'nullable|string',
        ];
    }
}
