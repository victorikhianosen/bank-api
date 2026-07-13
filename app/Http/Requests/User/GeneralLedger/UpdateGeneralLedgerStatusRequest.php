<?php

namespace App\Http\Requests\User\GeneralLedger;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralLedgerStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                    'disabled',
                    'blocked',
                    'deleted',
                    'pending'
                ])
            ]
        ];
    }
}