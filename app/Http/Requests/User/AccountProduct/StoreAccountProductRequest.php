<?php

namespace App\Http\Requests\User\AccountProduct;

use App\Enums\AccountProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            // 'code' => [
            //     'required',
            //     'string',
            //     'max:45',
            //     'unique:account_products,code'
            // ],

            'product_type' => [
                'required',
                'string',
                Rule::in(
                    AccountProductType::values()
                )
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'currency_id' => [
                'required',
                'exists:currencies,id'
            ],

            'interest_rate' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'interest_type' => [
                'required',
                Rule::in([
                    'flat',
                    'daily_balance',
                    'average_daily_balance',
                    'tiered'
                ])
            ],

            'interest_compounding_period' => [
                'nullable',
                Rule::in([
                    'daily',
                    'monthly',
                    'quarterly',
                    'annually'
                ])
            ],

            'interest_posting_period' => [
                'nullable',
                Rule::in([
                    'daily',
                    'monthly',
                    'quarterly',
                    'bi_annually',
                    'annually'
                ])
            ],

            'allow_overdraft' => [
                'nullable',
                'boolean'
            ],

            'overdraft_limit' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'overdraft_interest_rate' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'withhold_tax' => [
                'nullable',
                'boolean'
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                    'disabled',
                    'pending'
                ])
            ],

            /*
            |--------------------------------------------------------------------------
            | GENERAL LEDGER MAPPINGS
            |--------------------------------------------------------------------------
            */

            'general_ledgers' => [
                'required',
                'array',
                'min:1'
            ],

            'general_ledgers.*.financial_account_type' => [
                'required',
                'integer'
            ],

            'general_ledgers.*.financial_account_code' => [
                'required',
                'string'
            ],

            'general_ledgers.*.financial_account_name' => [
                'required',
                'string'
            ],

            'general_ledgers.*.general_ledger_id' => [
                'required',
                'exists:general_ledgers,id'
            ],
        ];
    }
}