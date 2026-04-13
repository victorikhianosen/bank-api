<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountProductSeeder extends Seeder
{
    public function run(): void
    {
        $savingsCategory = DB::table('account_categories')->where('code', 'SAV')->first();
        $currentCategory = DB::table('account_categories')->where('code', 'CUR')->first();

        $naira = DB::table('currencies')->where('code', 'NGN')->first();

        if (!$savingsCategory || !$currentCategory || !$naira) {
            throw new \Exception('Missing required seed data.');
        }

        DB::table('account_products')->insert([

            [
                'id' => Str::ulid(),
                'account_category_id' => $savingsCategory->id,
                'currency_id' => $naira->id,
                'name' => 'Individual Savings Account',
                'code' => 'SAV001',
                'interest_rate' => 3.50,
                'interest_type' => 'daily',
                'interest_posting' => 'monthly',
                'min_balance' => 1000.00,
                'max_balance' => null,
                'overdraft_allowed' => false,
                'overdraft_limit' => null,
                'daily_withdrawal_limit' => 500000.00,
                'max_transactions_per_day' => 10,
                'max_per_single_transaction' => 200000.00,
                'transfer_fee' => 10.00,
                'maintenance_fee' => 50.00,
                'dormancy_days' => 365,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => Str::ulid(),
                'account_category_id' => $savingsCategory->id,
                'currency_id' => $naira->id,
                'name' => 'Premium Savings Account',
                'code' => 'SAV002',
                'interest_rate' => 5.00,
                'interest_type' => 'daily',
                'interest_posting' => 'monthly',
                'min_balance' => 50000.00,
                'max_balance' => null,
                'overdraft_allowed' => false,
                'overdraft_limit' => null,
                'daily_withdrawal_limit' => 2000000.00,
                'max_transactions_per_day' => 20,
                'max_per_single_transaction' => 1000000.00,
                'transfer_fee' => 5.00,
                'maintenance_fee' => 100.00,
                'dormancy_days' => 365,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => Str::ulid(),
                'account_category_id' => $currentCategory->id,
                'currency_id' => $naira->id,
                'name' => 'Standard Current Account',
                'code' => 'CUR001',
                'interest_rate' => 0.00,
                'interest_type' => 'flat',
                'interest_posting' => 'monthly',
                'min_balance' => 0.00,
                'max_balance' => null,
                'overdraft_allowed' => true,
                'overdraft_limit' => 500000.00,
                'daily_withdrawal_limit' => 2000000.00,
                'max_transactions_per_day' => 50,
                'max_per_single_transaction' => 1000000.00,
                'transfer_fee' => 20.00,
                'maintenance_fee' => 100.00,
                'dormancy_days' => 365,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => Str::ulid(),
                'account_category_id' => $currentCategory->id,
                'currency_id' => $naira->id,
                'name' => 'Corporate Current Account',
                'code' => 'CUR002',
                'interest_rate' => 0.00,
                'interest_type' => 'flat',
                'interest_posting' => 'monthly',
                'min_balance' => 100000.00,
                'max_balance' => null,
                'overdraft_allowed' => true,
                'overdraft_limit' => 5000000.00,
                'daily_withdrawal_limit' => 10000000.00,
                'max_transactions_per_day' => 200,
                'max_per_single_transaction' => 5000000.00,
                'transfer_fee' => 50.00,
                'maintenance_fee' => 500.00,
                'dormancy_days' => 365,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}