<?php

namespace App\Http\Requests\User\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternalTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'source_account_no' => [
                'required',
                'exists:accounts,account_no'
            ],

            'destination_account_no' => [
                'required',
                'exists:accounts,account_no'
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1'
            ],

            'narration' => [
                'nullable',
                'string'
            ],

             'channel' => [
                'required',
                'string', 'in:admin,web,mobile'
            ],
        ];
    }
}