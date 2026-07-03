<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PlanRequest extends FormRequest
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
            'id' => 'required|numeric|digits_between:1,3',
            'name' => 'nullable|string|unique:type_plans,name',
            'qty_docs_invoice' => 'required|numeric|digits_between:1,10',
            'qty_docs_payroll' => 'required|numeric|digits_between:1,10',
            'qty_docs_radian' => 'required|numeric|digits_between:1,10',
            'qty_docs_ds' => 'required|numeric|digits_between:1,10',
            'period' => 'required|numeric|in:1,2,3',
            'state' => 'nullable|boolean',
            'observation' => 'nullable|string',
        ];
    }
}
