<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    use HttpResponses;
    public function __construct(
        protected CustomerService $customerService
    ) {}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'account_officer_id' => 'required|exists:account_officers,id',
            'account_product_id' => 'required|exists:account_products,id',
            'currency_id' => 'required|exists:currencies,id',

            'title' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'nullable|string',

            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|unique:customers,phone',
            'bvn' => 'nullable|string|unique:customers,bvn',

            'marital_status' => 'required|string',
            'gender' => 'required|string',
            'dob' => 'required|string',

            'occupation' => 'nullable|string',
            'working_status' => 'nullable|string',
            'business_name' => 'nullable|string',

            'nin_number' => 'nullable|string|unique:customers,nin_number',
            'tin' => 'nullable|string|unique:customers,tin',

            'address' => 'required|string',
            'lga' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',

            'mother_maiden_name' => 'nullable|string',
            'spouse_name' => 'nullable|string',

            'next_of_kin_name' => 'nullable|string',
            'next_of_kin_phone' => 'nullable|string',
            'next_of_kin_relationship' => 'nullable|string',
            'next_of_kin_address' => 'nullable|string',
        ]);

        $customer = $this->customerService->store($validated);
        return $this->success('Customer created successfully', $customer);
    }




    public function activate($id)
{
    try {
        $this->customerService->activate($id);

        return $this->success('Account activated successfully');

    } catch (\Exception $e) {

        Log::error('Error activating account', [
            'id' => $id,
            'message' => $e->getMessage()
        ]);

        return $this->error($e->getMessage(), 400);
    }
}
}
