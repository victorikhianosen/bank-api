<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LedgerAccount;
use App\Models\Currency;

class LedgerAccountSeeder extends Seeder
{
    public function run(): void
    {
        $currency = Currency::where('code', 'NGN')->first();

        if (!$currency) {
            throw new \Exception('NGN currency not found. Please run CurrencySeeder first.');
        }

        $ledgers = [
            [
                'name' => 'Cash Account',
                'code' => '10000001',
                'type' => 'asset',
                'purpose' => 'cash',
            ],
            [
                'name' => 'Savings Account',
                'code' => '20000001',
                'type' => 'liability',
                'purpose' => 'savings_control',
            ],
            [
                'name' => 'Interest Expense',
                'code' => '30000001',
                'type' => 'expense',
                'purpose' => 'interest_expense',
            ],
            [
                'name' => 'Operational Expense',
                'code' => '40000001',
                'type' => 'expense',
                'purpose' => 'interest_expense',
            ],
            [
                'name' => 'Fee Income',
                'code' => '50000001',
                'type' => 'income',
                'purpose' => 'fee_income',
            ],
            [
                'name' => 'Penalty Income',
                'code' => '60000001',
                'type' => 'income',
                'purpose' => 'penalty_income',
            ],
            [
                'name' => 'Loan Interest Income',
                'code' => '70000001',
                'type' => 'income',
                'purpose' => 'fee_income',
            ],
            [
                'name' => 'Savings Control',
                'code' => '80000001',
                'type' => 'liability',
                'purpose' => 'savings_control',
            ],
            [
                'name' => 'Customer Deposits',
                'code' => '90000001',
                'type' => 'liability',
                'purpose' => 'savings_control',
            ],
            [
                'name' => 'Bank Cash',
                'code' => '90000002',
                'type' => 'asset',
                'purpose' => 'cash',
            ],
            [
                'name' => 'Equity Capital',
                'code' => '90000003',
                'type' => 'equity',
                'purpose' => 'cash',
            ],
        ];

        foreach ($ledgers as $ledger) {
            LedgerAccount::updateOrCreate(
                ['code' => $ledger['code']],
                [
                    'branch_id' => null,
                    'currency_id' => $currency->id, 
                    'name' => $ledger['name'],
                    'type' => $ledger['type'],
                    'purpose' => $ledger['purpose'],
                    'parent_id' => null,
                    'balance' => 0,
                    'status' => 'active',
                    'created_by' => 'system',
                ]
            );
        }
    }
}