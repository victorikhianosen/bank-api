<?php

namespace App\Http\Requests\User\GeneralLedger;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGeneralLedgerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name')) {

            $this->merge([
                'name' => strtolower(
                    str_replace(
                        ' ',
                        '_',
                        trim($this->name)
                    )
                )
            ]);
        }

        if ($this->filled('type')) {

            $this->merge([
                'type' => strtolower(
                    str_replace(
                        ' ',
                        '_',
                        trim($this->type)
                    )
                )
            ]);
        }
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('general_ledgers', 'name')
            ],

            'gl_code' => [
                'required',
                'string',
                'max:45',
                Rule::unique('general_ledgers', 'gl_code')
            ],

            'currency_id' => [
                'nullable',
                'exists:currencies,id'
            ],

            'parent_id' => [
                'nullable',
                'exists:general_ledgers,id'
            ],

            'classification' => [
                'required',
                Rule::in([
                    'asset',
                    'liability',
                    'equity',
                    'income',
                    'expense'
                ])
            ],

            'type' => [
                'required',
                'string',
                'max:100'
            ],

            'manual_journal_entries_allowed' => [
                'nullable',
                'boolean'
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                    'disabled',
                    'blocked',
                    'deleted',
                    'pending'
                ])
            ],

            'description' => [
                'nullable',
                'string'
            ],
        ];
    }
}