<?php

namespace App\Http\Requests\User\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('id');

        return [

            'branch_id' => [
                'sometimes',
                'exists:branches,id'
            ],

            'account_officer_id' => [
                'sometimes',
                'exists:account_officers,id'
            ],

            'title' => [
                'nullable',
                'string'
            ],

            'first_name' => [
                'sometimes',
                'string'
            ],

            'last_name' => [
                'sometimes',
                'string'
            ],

            'middle_name' => [
                'nullable',
                'string'
            ],

            'email' => [
                'sometimes',
                'email',
                Rule::unique('customers', 'email')
                    ->ignore($customerId)
            ],

            'phone' => [
                'sometimes',
                Rule::unique('customers', 'phone')
                    ->ignore($customerId)
            ],

            'bvn' => [
                'nullable',
                Rule::unique('customers', 'bvn')
                    ->ignore($customerId)
            ],

            'nin_number' => [
                'nullable',
                Rule::unique('customers', 'nin_number')
                    ->ignore($customerId)
            ],

            'tin' => [
                'nullable',
                Rule::unique('customers', 'tin')
                    ->ignore($customerId)
            ],

            'marital_status' => [
                'nullable',
                'string'
            ],

            'gender' => [
                'nullable',
                'string'
            ],

            'dob' => [
                'nullable',
                'date'
            ],

            'occupation' => [
                'nullable',
                'string'
            ],

            'working_status' => [
                'nullable',
                'string'
            ],

            'business_name' => [
                'nullable',
                'string'
            ],

            'mother_maiden_name' => [
                'nullable',
                'string'
            ],

            'spouse_name' => [
                'nullable',
                'string'
            ],
        ];
    }
}