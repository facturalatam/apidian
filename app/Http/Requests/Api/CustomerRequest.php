<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
            // Customer
            'identification_number' => 'required|alpha_num|between:1,15',
            'dv' => 'nullable|numeric|digits:1',
            'name' => 'required|string',
            'phone' => 'required|numeric|digits_between:7,10',
            'address' => 'required|string',
            'email' => 'required|string|email',
            'sendnotification' => 'nullable|boolean',
        ];
    }
}
