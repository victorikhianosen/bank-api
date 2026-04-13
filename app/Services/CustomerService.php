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
                module: 'Account Request',
                action: 'create',
                auditableType: AccountRequest::class,
                auditableId: $accountRequest->id,
                before: null,
                after: $accountRequest->toArray(),
                description: "User {$authUser->id} created account request for {$customer->first_name} {$customer->last_name} ({$customer->customer_number})"
            );

            $this->handleDocuments($customer);

            return [
                'customer' => $customer,
                'account_request' => $accountRequest
            ];
        });
    }


    public function activate($id)
    {
        return DB::transaction(function () use ($id) {

            $user = Auth::guard('user')->user();

            $request = AccountRequest::where('id', $id)
                ->where('status', 'pending')
                ->first();

            if ($request) {
                $customer = $request->customer;
            } else {
                $customer = Customer::findOrFail($id);

                $request = AccountRequest::where('customer_id', $customer->id)
                    ->where('status', 'pending')
                    ->first();
            }

            if ($request->account_id) {
                throw new Exception('Request already approved');
            }

            $beforeCustomer = $customer->toArray();
            $beforeRequest = $request->toArray();

            if ($customer->status !== 'active') {

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
            }

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

            $this->audit->trail(
                module: 'Account',
                action: 'create',
                auditableType: Account::class,
                auditableId: $account->id,
                before: null,
                after: $account->toArray(),
                description: "User {$user->id} created account {$account->account_number} for {$customer->first_name} {$customer->last_name} ({$customer->customer_number})"
            );

            $request->account_id = $account->id;
            $request->status = 'approved';
            $request->approved_by_type = get_class($user);
            $request->approved_by_id = $user->id;
            $request->approved_at = now();
            $request->save();

            $this->audit->trail(
                module: 'Account Request',
                action: 'approve',
                auditableType: AccountRequest::class,
                auditableId: $request->id,
                before: $beforeRequest,
                after: $request->toArray(),
                description: "User {$user->id} approved account request and created account {$account->account_number} for {$customer->first_name} {$customer->last_name} ({$customer->customer_number})"
            );

            if ($customer->wasChanged('status')) {
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
            }

            return true;
        });
    }

    public function reject($customerId, $reason)
    {
        return DB::transaction(function () use ($customerId, $reason) {

            $user = Auth::guard('user')->user();

            $customer = Customer::findOrFail($customerId);

            $request = AccountRequest::where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->first();

            if (!$request) {
                throw new Exception('No pending account request found for this customer.');
            }

            $beforeRequest = $request->toArray();

            $request->status = 'rejected';
            $request->rejected_by_type = get_class($user);
            $request->rejected_by_id = $user->id;
            $request->rejected_at = now();
            $request->rejection_reason = $reason;
            $request->save();

            $this->audit->trail(
                module: 'Account Request',
                action: 'reject',
                auditableType: AccountRequest::class,
                auditableId: $request->id,
                before: $beforeRequest,
                after: $request->toArray(),
                description: "User {$user->id} rejected account request for {$customer->first_name} {$customer->last_name} ({$customer->customer_number}). Reason: {$reason}"
            );

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

    private function handleDocuments(Customer $customer)
    {
        $authUser = Auth::guard('user')->user();
        $request = request();

        $documents = [
            'profile_picture' => 'profile_picture',
            'nin' => 'nin',
            'utility_bill' => 'utility_bill',
        ];

        foreach ($documents as $input => $type) {

            if ($request->hasFile($input)) {

                $file = $request->file($input);
                $path = $file->store('documents/' . $customer->id, 'public');

                Document::create([
                    'customer_id' => $customer->id,
                    'account_id' => null,

                    'title' => strtoupper(str_replace('_', ' ', $type)),
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $type,
                    'status' => 'pending',

                    'uploaded_by_type' => get_class($authUser),
                    'uploaded_by_id' => $authUser->id,
                ]);
            }
        }
    }

    private function generateCustomerNumber(): string
    {
        do {
            $number = 'CUS' . mt_rand(10000000, 99999999);
        } while (Customer::where('customer_number', $number)->exists());

        return $number;
    }

    private function generateAccountNumber(string $productId): string
    {
        $product = AccountProduct::with('accountCategory')->findOrFail($productId);

        $prefix = AccountCategoryPrefix::fromCode($product->accountCategory->code);
        $number = str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);

        return $prefix . $number;
    }

    private function generateUsername(string $firstName, string $lastName): string
    {
        $base = strtolower($firstName . '.' . $lastName);
        $username = $base;
        $i = 1;

        while (Customer::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        return $username;
    }
}
