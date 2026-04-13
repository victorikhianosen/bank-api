<?php

namespace App\Services;

use App\Enums\AccountCategoryPrefix;
use App\Mail\CoreBankMail;
use App\Models\Account;
use App\Models\AccountProduct;
use App\Models\AccountRequest;
use App\Models\Customer;
use App\Models\Document;
use App\Services\AuditService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerService
{
    public function __construct(
        protected AuditService $audit
    ) {}

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $authUser = Auth::guard('user')->user();

            $customer = Customer::create([
                'branch_id' => $data['branch_id'],
                'account_officer_id' => $data['account_officer_id'],
                'customer_number' => $this->generateCustomerNumber(),

                'title' => $data['title'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null,

                'email' => $data['email'],
                'phone' => $data['phone'],

                'username' => $this->generateUsername($data['first_name'], $data['last_name']),

                'bvn' => $data['bvn'] ?? null,

                'marital_status' => $data['marital_status'],
                'gender' => $data['gender'],
                'dob' => $data['dob'],

                'occupation' => $data['occupation'] ?? null,
                'working_status' => $data['working_status'] ?? null,
                'business_name' => $data['business_name'] ?? null,

                'nin_number' => $data['nin_number'] ?? null,
                'tin' => $data['tin'] ?? null,

                'mother_maiden_name' => $data['mother_maiden_name'] ?? null,
                'spouse_name' => $data['spouse_name'] ?? null,

                'created_by_type' => get_class($authUser),
                'created_by_id' => $authUser->id,
            ]);

            $this->audit->trail(
    module: 'customer',
    action: 'create',
    auditableType: Customer::class,
    auditableId: $customer->id,
    before: null,
    after: $customer->toArray(),
    description: "User {$authUser->id} created customer {$customer->first_name} {$customer->last_name} ({$customer->customer_number})"
);

            $customer->addresses()->create([
                'type' => 'residential',
                'address' => $data['address'],
                'lga' => $data['lga'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? 'Nigeria',
                'is_primary' => true,
            ]);


            if (!empty($data['next_of_kin_name'])) {
                $customer->nextOfKins()->create([
                    'name' => $data['next_of_kin_name'],
                    'relationship' => $data['next_of_kin_relationship'] ?? null,
                    'address' => $data['next_of_kin_address'] ?? null,
                    'phone' => $data['next_of_kin_phone'] ?? null,
                ]);
            }

            $accountRequest = AccountRequest::create([
                'customer_id' => $customer->id,
                'account_product_id' => $data['account_product_id'],
                'currency_id' => $data['currency_id'],
                'branch_id' => $data['branch_id'],
                'created_by_type' => get_class($authUser),
                'created_by_id' => $authUser->id,
            ]);

            $this->audit->trail(
    module: 'account_request',
    action: 'create',
    auditableType: AccountRequest::class,
    auditableId: $accountRequest->id,
    before: null,
    after: $accountRequest->toArray(),
    description: "User {$authUser->id} created account request for {$customer->first_name} {$customer->last_name} ({$customer->customer_number})"
);

            $this->handleDocuments($customer);
            $this->audit->trail(
                module: 'customer',
                action: 'create',
                auditableType: Customer::class,
                auditableId: $customer->id,
                before: null,
                after: $customer->toArray(),
                description: "User {$authUser->id} created customer {$customer->first_name} {$customer->last_name} ({$customer->customer_number})"
            );

            return [
                'customer' => $customer,
                'account_request' => $accountRequest
            ];
        });
    }

 

    public function activate($customerId)
    {
        return DB::transaction(function () use ($customerId) {

            $user = Auth::guard('user')->user();

            $customer = Customer::findOrFail($customerId);

            if ($customer->status !== 'pending') {
                throw new Exception('Only pending customers can be activated');
            }

            $request = AccountRequest::where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->firstOrFail();

            $tempPassword = Str::random(8);

            $customer->status = 'active';
            $customer->approved_by_type = get_class($user);
            $customer->approved_by_id = $user->id;
            $customer->approved_at = now();
            $customer->password = bcrypt($tempPassword);
            $customer->enable_reset_password = true;
            $customer->save();

            $this->audit->trail(
    module: 'customer',
    action: 'activate',
    auditableType: Customer::class,
    auditableId: $customer->id,
    before: $beforeCustomer,
    after: $customer->toArray(),
    description: "User {$user->id} activated customer {$customer->first_name} {$customer->last_name} ({$customer->customer_number})"
);

            $account = Account::create([
                'customer_id' => $customer->id,
                'branch_id' => $request->branch_id,
                'account_product_id' => $request->account_product_id,
                'currency_id' => $request->currency_id,
                'account_number' => $this->generateAccountNumber($request->account_product_id),
                'account_name' => strtoupper($customer->first_name . ' ' . $customer->last_name),
                'status' => 'active',
                'approved_by_type' => get_class($user),
                'approved_by_id' => $user->id,
                'approved_at' => now(),
            ]);

            $request->account_id = $account->id;
            $request->status = 'approved';
            $request->approved_by_type = get_class($user);
            $request->approved_by_id = $user->id;
            $request->approved_at = now();
            $request->save();

            Mail::to($customer->email)->queue(
                new CoreBankMail(
                    subjectLine: 'Account Activated',
                    name: $customer->first_name,
                    content: "
                        Your account has been created.<br><br>
                        <strong>Account Number:</strong> {$account->account_number}<br>
                        <strong>Username:</strong> {$customer->username}<br>
                        <strong>Password:</strong> {$tempPassword}
                    "
                )
            );

            return true;
        });
    }

    /**
     * ✅ REJECT REQUEST
     */
    public function reject($customerId, $reason)
    {
        return DB::transaction(function () use ($customerId, $reason) {

            $user = Auth::guard('user')->user();

            $customer = Customer::findOrFail($customerId);

            $request = AccountRequest::where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->firstOrFail();

            $request->status = 'rejected';
            $request->rejected_by_type = get_class($user);
            $request->rejected_by_id = $user->id;
            $request->rejected_at = now();
            $request->rejection_reason = $reason;
            $request->save();

            Mail::to($customer->email)->queue(
                new CoreBankMail(
                    subjectLine: 'Account Request Rejected',
                    name: $customer->first_name,
                    content: "
                        Your account request has been rejected.<br><br>
                        <strong>Reason:</strong> {$reason}
                    "
                )
            );

            return true;
        });
    }

    /**
     * HELPERS
     */


}
