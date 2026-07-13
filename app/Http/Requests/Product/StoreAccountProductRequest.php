<?php

namespace App\Http\Requests\User\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => ['required', 'string', 'max:255'],

            'code' => ['required', 'string', 'max:45', 'unique:account_products,code'],

            'currency_id' => ['required', 'exists:currencies,id'],

            'interest_rate' => ['nullable', 'numeric'],

            'interest_type' => ['required'],

            'status' => ['nullable'],

            'description' => ['nullable', 'string'],

            /*
            |--------------------------------------------------------------------------
            | GL MAPPINGS
            |--------------------------------------------------------------------------
            */

            'gl_mappings' => ['required', 'array', 'min:1'],

            'gl_mappings.*.financial_account_type' => [
                'required',
                'integer'
            ],

            'gl_mappings.*.gl_account_id' => [
                'required',
                'exists:general_ledgers,id'
            ],
        ];
    }
}
