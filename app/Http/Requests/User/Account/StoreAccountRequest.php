<?php

namespace App\Http\Requests\User\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

    

            'customer_id' => [
                'required',
                'exists:customers,id'
            ],

            'account_product_id' => [
                'required',
                'exists:account_products,id'
            ],

     

            'branch_id' => [
                'required',
                'exists:branches,id'
            ],

            'account_officer_id' => [
                'required',
                'exists:account_officers,id'
            ],

            'tier_id' => [
                'nullable',
                'exists:tiers,id'
            ],

            'channel' => [
                'nullable',
                'string'
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'submitted',
                    'pending',
                    'approved',
                    'active',
                    'rejected',
                    'withdrawn',
                    'closed',
                    'dormant',
                    'frozen',
                    'restricted',
                ])
            ],
        ];
    }


}