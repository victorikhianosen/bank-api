<?php

namespace Database\Seeders;

use App\Models\Tier;
use Illuminate\Database\Seeder;

class TierSeeder extends Seeder
{
    public function run(): void
    {
        Tier::insert([

            [
                'name' => 'Tier 1',

                'level' => 1,

                'description' => 'Basic account with minimal KYC requirements',

                'daily_transaction_limit' => 50000,

                'daily_withdrawal_limit' => 50000,

                'daily_transfer_limit' => 50000,

                'max_account_balance' => 300000,

                'max_single_transaction_amount' => 50000,

                'max_daily_inflow' => 50000,

                'max_daily_outflow' => 50000,

                'require_bvn' => false,

                'require_nin' => false,

                'require_address_verification' => false,

                'require_face_verification' => false,

                'status' => 'active',

                'created_at' => now(),

                'updated_at' => now(),
            ],

            [
                'name' => 'Tier 2',

                'level' => 2,

                'description' => 'Intermediate account with BVN and NIN verification',

                'daily_transaction_limit' => 200000,

                'daily_withdrawal_limit' => 200000,

                'daily_transfer_limit' => 200000,

                'max_account_balance' => 500000,

                'max_single_transaction_amount' => 200000,

                'max_daily_inflow' => 200000,

                'max_daily_outflow' => 200000,

                'require_bvn' => true,

                'require_nin' => true,

                'require_address_verification' => false,

                'require_face_verification' => false,

                'status' => 'active',

                'created_at' => now(),

                'updated_at' => now(),
            ],

            [
                'name' => 'Tier 3',

                'level' => 3,

                'description' => 'Full KYC account with higher transaction limits',

                'daily_transaction_limit' => 5000000,

                'daily_withdrawal_limit' => 5000000,

                'daily_transfer_limit' => 5000000,

                'max_account_balance' => 50000000,

                'max_single_transaction_amount' => 5000000,

                'max_daily_inflow' => 5000000,

                'max_daily_outflow' => 5000000,

                'require_bvn' => true,

                'require_nin' => true,

                'require_address_verification' => true,

                'require_face_verification' => true,

                'status' => 'active',

                'created_at' => now(),

                'updated_at' => now(),
            ],

        ]);
    }
}