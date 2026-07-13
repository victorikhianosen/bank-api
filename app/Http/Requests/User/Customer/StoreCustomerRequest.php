<?php

namespace App\Http\Requests\User\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | ACCOUNT DETAILS
            |--------------------------------------------------------------------------
            */

            'branch_id' => [
                'required',
                'exists:branches,id'
            ],

            'account_officer_id' => [
                'required',
                'exists:account_officers,id'
            ],

     
            /*
            |--------------------------------------------------------------------------
            | CUSTOMER DETAILS
            |--------------------------------------------------------------------------
            */
    
            'title' => [
                'required',
                'string',
                'max:20'
            ],

            'first_name' => [
                'required',
                'string',
                'max:100'
            ],

            'last_name' => [
                'required',
                'string',
                'max:100'
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:100'
            ],

            'email' => [
                'required',
                'email',
                'unique:customers,email'
            ],

            'phone' => [
                'required',
                'string',
                'unique:customers,phone'
            ],

            'bvn' => [
                'nullable',
                'string',
                'unique:customers,bvn'
            ],

            'nin_number' => [
                'nullable',
                'string',
                'unique:customers,nin_number'
            ],

            'tin' => [
                'nullable',
                'string',
                'unique:customers,tin'
            ],

            'marital_status' => [
                'required',
                Rule::in([
                    'single',
                    'married',
                    'divorced',
                    'widowed'
                ])
            ],

            'gender' => [
                'required',
                Rule::in([
                    'male',
                    'female'
                ])
            ],

            'dob' => [
                'required',
                'date'
            ],

            /*
            |--------------------------------------------------------------------------
            | EMPLOYMENT
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | ADDRESS
            |--------------------------------------------------------------------------
            */

            'address' => [
                'required',
                'string'
            ],

            'lga' => [
                'nullable',
                'string'
            ],

            'state' => [
                'nullable',
                'string'
            ],

            'country' => [
                'nullable',
                'string'
            ],

            /*
            |--------------------------------------------------------------------------
            | FAMILY
            |--------------------------------------------------------------------------
            */

            'mother_maiden_name' => [
                'nullable',
                'string'
            ],

            'spouse_name' => [
                'nullable',
                'string'
            ],

            /*
            |--------------------------------------------------------------------------
            | NEXT OF KIN
            |--------------------------------------------------------------------------
            */

            'next_of_kin_name' => [
                'nullable',
                'string'
            ],

            'next_of_kin_phone' => [
                'nullable',
                'string'
            ],

            'next_of_kin_relationship' => [
                'nullable',
                'string'
            ],

            'next_of_kin_address' => [
                'nullable',
                'string'
            ],

            /*
            |--------------------------------------------------------------------------
            | DOCUMENTS
            |--------------------------------------------------------------------------
            */

            'profile_picture' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png'
            ],

            'nin' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf'
            ],

            'utility_bill' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf'
            ],


            
        ];
    }
}