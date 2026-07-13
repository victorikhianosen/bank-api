<?php

namespace App\Http\Requests\User\GeneralLedger;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralLedgerRequest extends FormRequest
{
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

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ledgerId = $this->route('id');

        return [

            'name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('general_ledgers', 'name')
                    ->ignore($ledgerId)
            ],

            'gl_code' => [
                'nullable',
                'string',
                'max:45',
                Rule::unique('general_ledgers', 'gl_code')
                    ->ignore($ledgerId)
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
                'nullable',
                Rule::in([
                    'asset',
                    'liability',
                    'equity',
                    'income',
                    'expense'
                ])
            ],

            'type' => [
                'nullable',
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