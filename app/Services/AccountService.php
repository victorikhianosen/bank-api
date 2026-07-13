<?php

namespace App\Services;

use App\Mail\CoreBankMail;
use App\Models\Account;
use App\Models\AccountProduct;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountService
{
    public function __construct(
        private AuditService $audit
    ) {}

    public function store(array $data): Account
    {
        return DB::transaction(function () use ($data) {

            // Get customer

            $customer = Customer::findOrFail(
                $data['customer_id']
            );

            // Get product

            $product = AccountProduct::findOrFail(
                $data['account_product_id']
            );

            // Validate customer

            $this->validateCustomer(
                $customer
            );

            // Check existing account

            $hasExistingAccount = Account::where(
                'customer_id',
                $customer->id
            )->exists();

            // Create account

            $account = $this->createAccount(
                customer: $customer,
                product: $product,
                data: $data
            );

            // Send mail

            if ($hasExistingAccount) {

                $this->sendExistingCustomerMail(
                    customer: $customer,
                    account: $account,
                    product: $product
                );
            } else {

                $this->sendNewCustomerMail(
                    customer: $customer,
                    account: $account,
                    product: $product
                );
            }

            // Audit log

            $this->audit->trail(
                module: 'account',
                action: 'create',
                auditableType: Account::class,
                auditableId: $account->id,
                before: null,
                after: $account->toArray(),
                description: "Account {$account->account_no} created"
            );

            return $account->load([
                'customer',
                'product'
            ]);
        });
    }

    // Validate customer

    private function validateCustomer(
        Customer $customer
    ): void {

        if ($customer->status === 'closed') {

            throw ValidationException::withMessages([
                'customer_id' => [
                    'Closed customer cannot own account'
                ]
            ]);
        }
    }

    // Create account

    private function createAccount(
        Customer $customer,
        AccountProduct $product,
        array $data
    ): Account {

        return Account::create([

            'account_no' =>
                $this->generateAccountNumber(),

            'account_name' =>
                trim(
                    $customer->first_name .
                    ' ' .
                    $customer->last_name
                ),

            'customer_id' =>
                $customer->id,

            'customer_number' =>
                $customer->customer_number,

            'account_product_id' =>
                $product->id,

            'branch_id' =>
                $data['branch_id'],

            'account_officer_id' =>
                $data['account_officer_id'],

            'currency_id' =>
                $product->currency_id,

            'tier_id' =>
                $data['tier_id'] ?? 1,

            'submitted_by' =>
                Auth::guard('user')->id(),

            'submitted_at' =>
                now(),

            'channel' =>
                $data['channel'] ?? 'web',

            'status' =>
                $data['status'] ?? 'pending',
        ]);
    }

    // Send mail for new customer

    private function sendNewCustomerMail(
        Customer $customer,
        Account $account,
        AccountProduct $product
    ): void {

        // Generate password

        $plainPassword = strtoupper(
            Str::random(10)
        );

        // Update customer

        $customer->update([

            'password' => Hash::make(
                $plainPassword
            ),

            'enable_reset_password' => true,
        ]);

        // Build mail content

        $content = "
            Your account has been successfully created <br><br>

            Account Number: {$account->account_no} <br>

            Username: {$customer->username}<br>

            Temporary Password: {$plainPassword}<br>

            Account Type: " . Str::headline($product->name) . "<br>

            Please login and change your password immediately.
        ";

        // Send mail

        Mail::to($customer->email)->queue(

            new CoreBankMail(
                subjectLine: 'Account Creation Notification',
                content: $content,
                name: $customer->first_name
            )
        );
    }

    // Send mail for existing customer

    private function sendExistingCustomerMail(
        Customer $customer,
        Account $account,
        AccountProduct $product
    ): void {

        $content = "
            A new account has been created successfully.<br><br>

            Account Number: {$account->account_no}<br>

            Account Type: " . Str::headline($product->name) . "<br><br>
        ";

        Mail::to($customer->email)->queue(

            new CoreBankMail(
                subjectLine: 'New Account Notification',
                content: $content,
                name: $customer->first_name
            )
        );
    }

    // Generate account number

    private function generateAccountNumber(): string
    {
        do {

            $accountNumber = mt_rand(
                1000000000,
                9999999999
            );

        } while (
            Account::where(
                'account_no',
                $accountNumber
            )->exists()
        );

        return (string) $accountNumber;
    }
}