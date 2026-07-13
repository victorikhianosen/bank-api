<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Customer\StoreCustomerRequest;
use App\Http\Requests\User\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    use HttpResponses;

    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('customer_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

        $customers = $query
            ->latest()
            ->paginate(20);

        return $this->success(
            'Customers fetched successfully',
            $customers
        );
    }

    public function store(
        StoreCustomerRequest $request
    ) {
        try {

            $customer = $this->customerService->store(
                $request->validated()
            );

            return $this->success(
                'Customer created successfully',
                $customer
            );
        } catch (\Exception $e) {

            Log::error('Customer creation failed', [
                'message' => $e->getMessage(),
            ]);

            return $this->error(
                $e->getMessage(),
                400
            );
        }
    }

    public function show($id)
    {
            $customer = Customer::with([
                'addresses',
                'nextOfKins',
                'documents'
            ])->findOrFail($id);

            return $this->success(
                'Customer fetched successfully',
                $customer
            );
        

     
    }

    public function update(
        UpdateCustomerRequest $request,
        $id
    ) {
        try {

            $customer = $this->customerService->update(
                $id,
                $request->validated()
            );

            return $this->success(
                'Customer updated successfully',
                $customer
            );
        } catch (\Exception $e) {

            return $this->error(
                $e->getMessage(),
                400
            );
        }
    }

    public function destroy($id)
    {
        try {

            $customer = Customer::findOrFail($id);

            $customer->delete();

            return $this->success(
                'Customer deleted successfully'
            );
        } catch (\Exception $e) {

            return $this->error(
                $e->getMessage(),
                400
            );
        }
    }

    public function resetPassword($id)
    {
        try {

            $this->customerService->resetPassword($id);

            return $this->success(
                'Password reset successfully'
            );
        } catch (\Exception $e) {

            return $this->error(
                $e->getMessage(),
                400
            );
        }
    }

    public function resetPin($id)
    {
        try {

            $this->customerService->resetPin($id);

            return $this->success(
                'PIN reset successfully'
            );
        } catch (\Exception $e) {

            return $this->error(
                $e->getMessage(),
                400
            );
        }
    }

    public function uploadDocument(
        Request $request,
        $id
    ) {
        try {

            $request->validate([
                'file' => 'required|file',
                'type' => 'required|string',
            ]);

            $this->customerService->uploadDocument(
                $id,
                $request
            );

            return $this->success(
                'Document uploaded successfully'
            );
        } catch (\Exception $e) {

            return $this->error(
                $e->getMessage(),
                400
            );
        }
    }

    public function toggleNotifications(
        Request $request,
        $id
    ) {
        try {

            $validated = $request->validate([

                'enable_email' => 'nullable|boolean',

                'enable_sms' => 'nullable|boolean',
            ]);

            $customer = $this->customerService
                ->toggleNotifications(
                    $id,
                    $validated
                );

            return $this->success(
                'Notification settings updated',
                $customer
            );
        } catch (\Exception $e) {

            return $this->error(
                $e->getMessage(),
                400
            );
        }
    }
}
