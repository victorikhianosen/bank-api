<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            // Personal Info
            'title' => 'required|string|max:10',
            'surname' => 'required|string|max:100',
            'other_names' => 'required|string|max:100',
            'marital_status' => 'required|in:single,married,others',
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'required|email|unique:customers,email',
            'dob' => 'required|date',
            'mother_maiden_name' => 'required|string',
            'spouse_name' => 'nullable|string',

            // Address & Origin
            'residential_address' => 'required|string',
            'postal_address' => 'nullable|string',
            'nationality' => 'required|string',
            'state_of_origin' => 'required|string',
            'lga_of_origin' => 'required|string',

            // Work
            'place_of_work' => 'nullable|string',
            'work_address' => 'nullable|string',

            // ID Details
            'id_type' => 'required|in:International Passport,Drivers Licence,National ID,Others',
            'id_number' => 'required|string|unique:customers,id_number',
            'id_issue_date' => 'required|date',
            'id_place_of_issue' => 'required|string',

            // Next of Kin
            'nok_name' => 'required|string',
            'nok_relationship' => 'required|string',
            'nok_address' => 'required|string',
            'nok_phone' => 'required|string',

            // Bank Details
            'bank_name' => 'required|string',
            'branch_sort_code' => 'required|string',
            'account_type' => 'required|string',
            'account_number' => 'required|string|size:10',
            'bvn' => 'required|string|size:11|unique:customers,bvn',
            
            // Files (Validation for uploads)
            'id_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passport_photo' => 'nullable|image|max:2048',
            'utility_bill' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}