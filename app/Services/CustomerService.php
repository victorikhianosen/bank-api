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
use App\Services\NotificationService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CustomerService
{
    public function __construct(
        protected AuditService $audit,
        protected NotificationService $notification
    ) {}

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $authUser = Auth::guard('user')->user();

            $customer = Customer::create([
                'branch_id' => $data['branch_id'],
                'account_officer_id' => $data['account_officer_id'],
                'customer_number' => $this->generateCustomerNumber(),
                'referral_code' => $this->generateReferralCode(),
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


            $this->handleDocuments($customer);

            return [
                'customer' => $customer,
            ];
        });
    }


    public function update($id, $data)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new Exception('Customer not found');
        }

        $user = Auth::guard('user')->user();

        $before = $customer->toArray();

        unset($data['password'], $data['pin']);

        $customer->update($data);

        $this->audit->trail(
            module: 'customer',
            action: 'update',
            auditableType: Customer::class,
            auditableId: $customer->id,
            before: $before,
            after: $customer->fresh()->toArray(),
            description: "User {$user->id} updated customer {$customer->first_name} {$customer->last_name}"
        );

        return $customer->fresh();
    }



    public function resetPassword($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new Exception('Customer not found');
        }

        $user = Auth::guard('user')->user();

        $before = $customer->toArray();

        $password = Str::random(8);

        $customer->password = bcrypt($password);
        $customer->enable_reset_password = true;
        $customer->save();

        $this->audit->trail(
            module: 'customer',
            action: 'reset_password',
            auditableType: Customer::class,
            auditableId: $customer->id,
            before: $before,
            after: $customer->toArray(),
            description: "User {$user->id} reset password for {$customer->first_name} {$customer->last_name}"
        );

        $this->notification->sendEmail($customer, 'Password Reset', "Your new password is: {$password}");
    }


    public function resetPin($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new Exception('Customer not found');
        }

        $user = Auth::guard('user')->user();

        $before = $customer->toArray();

        $pin = rand(1000, 9999);

        $customer->pin = bcrypt($pin);
        $customer->save();

        $this->audit->trail(
            module: 'customer',
            action: 'reset_pin',
            auditableType: Customer::class,
            auditableId: $customer->id,
            before: $before,
            after: $customer->toArray(),
            description: "User {$user->id} reset PIN for {$customer->first_name} {$customer->last_name}"
        );

        $this->notification->sendEmail($customer, 'PIN Reset', "Your new PIN is: {$pin}");
    }


    public function uploadDocument($id, $request)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new Exception('Customer not found');
        }

        $file = $request->file('file');
        $path = $file->store('documents/' . $customer->id, 'public');

        Document::create([
            'customer_id' => $customer->id,
            'title' => strtoupper($request->type),
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'type' => $request->type,
            'status' => 'pending',
        ]);
    }


    public function toggleNotifications($id, $data)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            throw new Exception('Customer not found');
        }
        $user = Auth::guard('user')->user();
        $before = $customer->toArray();
        // apply changes
        if (isset($data['enable_email'])) {
            $customer->enable_email = $data['enable_email'];
        }
        if (isset($data['enable_sms'])) {
            $customer->enable_sms = $data['enable_sms'];
        }
        $customer->save();
        $this->audit->trail(
            module: 'customer',
            action: 'update_notification_settings',
            auditableType: Customer::class,
            auditableId: $customer->id,
            before: $before,
            after: $customer->toArray(),
            description: "User {$user->id} updated notification settings (email: {$customer->enable_email}, sms: {$customer->enable_sms}) for {$customer->first_name} {$customer->last_name}"
        );
        return $customer->fresh();
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
        return DB::transaction(function () {
            $lastCustomer = Customer::lockForUpdate()
                ->where('customer_number', 'LIKE', 'CUS%')
                ->orderByDesc('id')
                ->first();
            $lastNumber = 0;
            if ($lastCustomer) {
                $lastNumber = (int) str_replace('CUS', '', $lastCustomer->customer_number);
            }
            $newNumber = $lastNumber + 1;
            return 'CUS' . str_pad($newNumber, 10, '0', STR_PAD_LEFT);
        });
    }

    private function generateUsername(string $firstName, string $lastName): string
    {
        $base = strtolower(
            preg_replace('/[^a-zA-Z0-9]/', '', $firstName . $lastName)
        );
        $username = $base;
        while (Customer::where('username', $username)->exists()) {
            $username = $base . rand(10, 99);
        }
        return $username;
    }

    private function generateReferralCode(): string
    {
        return DB::transaction(function () {
            do {
                $code = 'REF' . strtoupper(Str::random(10));
            } while (
                Customer::lockForUpdate()
                ->where('referral_code', $code)
                ->exists()
            );
            return $code;
        });
    }


}
