<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\GeneralLedger;
use Illuminate\Database\Seeder;

class DomiciliaryGeneralLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $usd = Currency::where(
            'code',
            'USD'
        )->firstOrFail();

        $generalLedgers = [

            /*
            |--------------------------------------------------------------------------
            | ASSET GENERAL LEDGERS
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'usd_settlement_balance',
                'gl_code' => '110100',
                'classification' => 'asset',
                'type' => 'usd_settlement_balance',
                'description' => 'USD settlement balance account',
            ],

            [
                'name' => 'usd_vault_balance',
                'gl_code' => '110200',
                'classification' => 'asset',
                'type' => 'usd_vault_balance',
                'description' => 'USD vault balance account',
            ],

            [
                'name' => 'usd_atm_balance',
                'gl_code' => '110300',
                'classification' => 'asset',
                'type' => 'usd_atm_balance',
                'description' => 'USD ATM balance account',
            ],

            [
                'name' => 'usd_overdraft_balance',
                'gl_code' => '110400',
                'classification' => 'asset',
                'type' => 'usd_overdraft_balance',
                'description' => 'USD overdraft balance account',
            ],

            /*
            |--------------------------------------------------------------------------
            | LIABILITY GENERAL LEDGERS
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'usd_savings_balance',
                'gl_code' => '210100',
                'classification' => 'liability',
                'type' => 'usd_savings_balance',
                'description' => 'USD savings account balance',
            ],

            [
                'name' => 'usd_current_balance',
                'gl_code' => '210200',
                'classification' => 'liability',
                'type' => 'usd_current_balance',
                'description' => 'USD current account balance',
            ],

            [
                'name' => 'usd_merchant_balance',
                'gl_code' => '210300',
                'classification' => 'liability',
                'type' => 'usd_merchant_balance',
                'description' => 'USD merchant account balance',
            ],

            [
                'name' => 'usd_corporate_balance',
                'gl_code' => '210400',
                'classification' => 'liability',
                'type' => 'usd_corporate_balance',
                'description' => 'USD corporate account balance',
            ],

            [
                'name' => 'usd_domiciliary_balance',
                'gl_code' => '210500',
                'classification' => 'liability',
                'type' => 'usd_domiciliary_balance',
                'description' => 'USD domiciliary account balance',
            ],

            [
                'name' => 'usd_interest_payable',
                'gl_code' => '210600',
                'classification' => 'liability',
                'type' => 'usd_interest_payable',
                'description' => 'USD interest payable account',
            ],

            [
                'name' => 'usd_withholding_tax_payable',
                'gl_code' => '210700',
                'classification' => 'liability',
                'type' => 'usd_withholding_tax_payable',
                'description' => 'USD withholding tax payable account',
            ],

            [
                'name' => 'usd_suspense_balance',
                'gl_code' => '210800',
                'classification' => 'liability',
                'type' => 'usd_suspense_balance',
                'description' => 'USD suspense balance account',
            ],

            /*
            |--------------------------------------------------------------------------
            | EQUITY GENERAL LEDGERS
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'usd_retained_earnings',
                'gl_code' => '310100',
                'classification' => 'equity',
                'type' => 'usd_retained_earnings',
                'description' => 'USD retained earnings account',
            ],

            /*
            |--------------------------------------------------------------------------
            | INCOME GENERAL LEDGERS
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'usd_transfer_fee_income',
                'gl_code' => '410100',
                'classification' => 'income',
                'type' => 'usd_transfer_fee_income',
                'description' => 'USD transfer fee income',
            ],

            [
                'name' => 'usd_sms_fee_income',
                'gl_code' => '410200',
                'classification' => 'income',
                'type' => 'usd_sms_fee_income',
                'description' => 'USD SMS fee income',
            ],

            [
                'name' => 'usd_maintenance_fee_income',
                'gl_code' => '410300',
                'classification' => 'income',
                'type' => 'usd_maintenance_fee_income',
                'description' => 'USD maintenance fee income',
            ],

            [
                'name' => 'usd_penalty_income',
                'gl_code' => '410400',
                'classification' => 'income',
                'type' => 'usd_penalty_income',
                'description' => 'USD penalty income',
            ],

            [
                'name' => 'usd_overdraft_interest_income',
                'gl_code' => '410500',
                'classification' => 'income',
                'type' => 'usd_overdraft_interest_income',
                'description' => 'USD overdraft interest income',
            ],

            /*
            |--------------------------------------------------------------------------
            | EXPENSE GENERAL LEDGERS
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'usd_interest_expense',
                'gl_code' => '510100',
                'classification' => 'expense',
                'type' => 'usd_interest_expense',
                'description' => 'USD customer interest expense',
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

                    'currency_id' => $usd->id,

                    'currency_code' => $usd->code,

                    'classification' => $ledger['classification'],

                    'type' => $ledger['type'],

                    'manual_journal_entries_allowed' => true,

                    'status' => 'active',

                    'description' => $ledger['description'],
                ]
            );

            if (! $generalLedger->hierarchy) {

                $generalLedger->update([
                    'hierarchy' => (string) $generalLedger->id
                ]);
            }
        }
    }
}