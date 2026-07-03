<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ConfigurationRequest extends FormRequest
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
     * Get data to be validated from the request. From Route URL.
     *
     * @return array
     */
    public function validationData()
    {
        if (method_exists($this->route(), 'parameters')) {
            $this->request->add($this->route()->parameters());
            $this->query->add($this->route()->parameters());

            return array_merge($this->route()->parameters(), $this->all());
        }

        return $this->all();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
//            'nit' => 'required|numeric|digits_between:1,15|unique:companies,identification_number',
            'nit' => 'required|numeric|digits_between:1,15',
            'dv' => 'required|numeric|digits:1|dian_dv:'.$this->nit,
            'language_id' => 'nullable|exists:languages,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'type_environment_id' => 'nullable|exists:type_environments,id',
            'payroll_type_environment_id' => 'nullable|exists:type_environments,id',
            'eqdocs_type_environment_id' => 'nullable|exists:type_environments,id',
            'type_operation_id' => 'nullable|exists:type_operations,id',
            'type_document_identification_id' => 'required|exists:type_document_identifications,id',
            'country_id' => 'nullable|exists:countries,id',
            'type_currency_id' => 'nullable|exists:type_currencies,id',
            'type_organization_id' => 'required|exists:type_organizations,id',
            'type_regime_id' => 'required|exists:type_regimes,id',
            'type_liability_id' => 'required|exists:type_liabilities,id',
            'type_liability_ids' => 'nullable|array|min:1',
            'type_liability_ids.*' => 'integer|exists:type_liabilities,id',
            'business_name' => 'required|string',
            'municipality_id' => 'required|exists:municipalities,id',
            'merchant_registration' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|numeric|digits_between:7,10',
//            'email' => 'required|string|email|unique:users,email',
            'email' => 'required|string|email',
            'id_administrador' => 'nullable|exists:administrators,id',
            'type_plan_id' => 'nullable|integer|min:1|exists:type_plans,id',
            'type_plan2_id' => 'nullable|integer|min:1|exists:type_plans,id',
            'type_plan3_id' => 'nullable|integer|min:1|exists:type_plans,id',
            'type_plan4_id' => 'nullable|integer|min:1|exists:type_plans,id',
            'absolut_plan_documents' => 'nullable|integer',
            'renew_plan1' => 'nullable|boolean',
            'renew_plan2' => 'nullable|boolean',
            'renew_plan3' => 'nullable|boolean',
            'renew_plan4' => 'nullable|boolean',
            'renew_absolut_plan' => 'nullable|boolean',
            'state' => 'nullable|boolean',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|required_with:mail_host,mail_username,mail_password,mail_encryption|numeric',
            'mail_username' => 'nullable|required_with:mail_host,mail_port,mail_password,mail_encryption|string',
            'mail_password' => 'nullable|required_with:mail_host,mail_port,mail_username,mail_encryption|string',
            'mail_encryption' => 'nullable|required_with:mail_host,mail_port,mail_username,mail_password|string',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',
        ];
    }
}
