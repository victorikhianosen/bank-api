<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\GeneralLedger;
use Illuminate\Database\Seeder;

class GeneralLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $currency = Currency::where(
            'code',
            'NGN'
        )->firstOrFail();

        $generalLedgers = [

            [
                'name' => 'settlement_balance',
                'gl_code' => '100100',
                'classification' => 'asset',
                'type' => 'settlement_balance',
                'description' => 'Main settlement balance account',
            ],

            [
                'name' => 'vault_balance',
                'gl_code' => '100200',
                'classification' => 'asset',
                'type' => 'vault_balance',
                'description' => 'Vault balance account',
            ],

            [
                'name' => 'atm_balance',
                'gl_code' => '100300',
                'classification' => 'asset',
                'type' => 'atm_balance',
                'description' => 'ATM settlement balance',
            ],

            [
                'name' => 'overdraft_balance',
                'gl_code' => '100400',
                'classification' => 'asset',
                'type' => 'overdraft_balance',
                'description' => 'Customer overdraft balance',
            ],

            [
                'name' => 'savings_balance',
                'gl_code' => '200100',
                'classification' => 'liability',
                'type' => 'savings_balance',
                'description' => 'Savings account balance',
            ],

            [
                'name' => 'current_balance',
                'gl_code' => '200200',
                'classification' => 'liability',
                'type' => 'current_balance',
                'description' => 'Current account balance',
            ],

            [
                'name' => 'merchant_balance',
                'gl_code' => '200300',
                'classification' => 'liability',
                'type' => 'merchant_balance',
                'description' => 'Merchant account balance',
            ],

            [
                'name' => 'corporate_balance',
                'gl_code' => '200400',
                'classification' => 'liability',
                'type' => 'corporate_balance',
                'description' => 'Corporate account balance',
            ],

            [
                'name' => 'wallet_balance',
                'gl_code' => '200500',
                'classification' => 'liability',
                'type' => 'wallet_balance',
                'description' => 'Wallet account balance',
            ],

            [
                'name' => 'domiciliary_balance',
                'gl_code' => '200600',
                'classification' => 'liability',
                'type' => 'domiciliary_balance',
                'description' => 'Domiciliary account balance',
            ],

            [
                'name' => 'interest_payable',
                'gl_code' => '200700',
                'classification' => 'liability',
                'type' => 'interest_payable',
                'description' => 'Interest payable account',
            ],

            [
                'name' => 'withholding_tax_payable',
                'gl_code' => '200800',
                'classification' => 'liability',
                'type' => 'withholding_tax_payable',
                'description' => 'Withholding tax payable',
            ],

            [
                'name' => 'suspense_balance',
                'gl_code' => '200900',
                'classification' => 'liability',
                'type' => 'suspense_balance',
                'description' => 'Suspense balance account',
            ],

            [
                'name' => 'share_capital',
                'gl_code' => '300100',
                'classification' => 'equity',
                'type' => 'share_capital',
                'description' => 'Share capital account',
            ],

            [
                'name' => 'retained_earnings',
                'gl_code' => '300200',
                'classification' => 'equity',
                'type' => 'retained_earnings',
                'description' => 'Retained earnings account',
            ],

            [
                'name' => 'transfer_fee_income',
                'gl_code' => '400100',
                'classification' => 'income',
                'type' => 'transfer_fee_income',
                'description' => 'Transfer fee income',
            ],

            [
                'name' => 'sms_fee_income',
                'gl_code' => '400200',
                'classification' => 'income',
                'type' => 'sms_fee_income',
                'description' => 'SMS fee income',
            ],

            [
                'name' => 'maintenance_fee_income',
                'gl_code' => '400300',
                'classification' => 'income',
                'type' => 'maintenance_fee_income',
                'description' => 'Account maintenance fee income',
            ],

            [
                'name' => 'penalty_income',
                'gl_code' => '400400',
                'classification' => 'income',
                'type' => 'penalty_income',
                'description' => 'Penalty income',
            ],

            [
                'name' => 'overdraft_interest_income',
                'gl_code' => '400500',
                'classification' => 'income',
                'type' => 'overdraft_interest_income',
                'description' => 'Overdraft interest income',
            ],

            [
                'name' => 'interest_expense',
                'gl_code' => '500100',
                'classification' => 'expense',
                'type' => 'interest_expense',
                'description' => 'Customer interest expense',
            ],
        ];

        foreach ($generalLedgers as $ledger) {

            $generalLedger = GeneralLedger::updateOrCreate(
                [
                    'gl_code' => $ledger['gl_code']
                ],
                [
                    'name' => $ledger['name'],

                    'balance' => 0,

                    'currency_id' => $currency->id,

                    'currency_code' => $currency->code,

                    'classification' => $ledger['classification'],

                    'type' => $ledger['type'],

                    'manual_journal_entries_allowed' => true,

                    'status' => 'active',

                    'description' => $ledger['description'],
                ]
            );

            if (!$generalLedger->hierarchy) {

                $generalLedger->update([
                    'hierarchy' => (string) $generalLedger->id
                ]);
            }
        }
    }
}