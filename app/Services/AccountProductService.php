<?php

namespace App\Services;

use App\Models\AccountProduct;
use App\Models\Currency;
use App\Models\GeneralLedger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountProductService
{
    public function __construct(
        private AuditService $audit
    ) {}

    public function store(array $data): AccountProduct
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | REQUIRED FINANCIAL ACCOUNT TYPES
            |--------------------------------------------------------------------------
            */

            $requiredFinancialTypes = [

                1 => 'Customer Deposit Control',

                2 => 'Cash / Settlement',

                3 => 'Interest Expense',

                4 => 'Interest Payable',

                5 => 'Fee Income',

                6 => 'Penalty Income',

                7 => 'Withholding Tax',

                8 => 'Overdraft Receivable',

                9 => 'Overdraft Interest Income',
            ];

            $providedTypes = collect(
                $data['general_ledgers']
            )->pluck(
                'financial_account_type'
            )->toArray();

            foreach ($requiredFinancialTypes as $type => $name) {

                if (! in_array($type, $providedTypes)) {

                    throw ValidationException::withMessages([
                        'general_ledgers' => [
                            "{$name} GL mapping is required"
                        ]
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDATE GENERAL LEDGER CURRENCY
            |--------------------------------------------------------------------------
            */

            foreach ($data['general_ledgers'] as $mapping) {

                $ledger = GeneralLedger::findOrFail(
                    $mapping['general_ledger_id']
                );

                $productCurrency = Currency::findOrFail(
                    $data['currency_id']
                );

                if ($ledger->currency_id != $productCurrency->id) {

                    throw ValidationException::withMessages([
                        'general_ledgers' => [

                            "Currency mismatch for ledger '{$ledger->name}'. " .

                                "Product currency is {$productCurrency->code} " .

                                "but ledger currency is {$ledger->currency_code}."
                        ]
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | GET CURRENCY
            |--------------------------------------------------------------------------
            */

            $currency = Currency::findOrFail(
                $data['currency_id']
            );

            /*
            |--------------------------------------------------------------------------
            | CREATE PRODUCT
            |--------------------------------------------------------------------------
            */

            $product = AccountProduct::create([

                'name' => strtolower(
                    str_replace(' ', '_', trim($data['name']))
                ),

                'code' => $this->generateProductCode(
                    $data['product_type']
                ),

                'product_type' => strtolower(
                    str_replace(' ', '_', trim($data['product_type']))
                ),

                'description' => $data['description'] ?? null,

                'currency_id' => $currency->id,

                'currency_code' => $currency->code,

                'currency_digits' => $currency->decimal_places ?? 2,

                'interest_rate' => $data['interest_rate'] ?? 0,

                'interest_type' => $data['interest_type'],

                'interest_compounding_period' =>
                $data['interest_compounding_period'] ?? null,

                'interest_posting_period' =>
                $data['interest_posting_period'] ?? null,

                'allow_overdraft' =>
                $data['allow_overdraft'] ?? false,

                'overdraft_limit' =>
                $data['overdraft_limit'] ?? 0,

                'overdraft_interest_rate' =>
                $data['overdraft_interest_rate'] ?? 0,

                'withhold_tax' =>
                $data['withhold_tax'] ?? false,

                'status' =>
                $data['status'] ?? 'pending',

                'created_by' => Auth::guard('user')->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | CREATE GL MAPPINGS
            |--------------------------------------------------------------------------
            */

            foreach ($data['general_ledgers'] as $mapping) {

                $product->generalLedgerMappings()->create([

                    'general_ledger_id' =>
                    $mapping['general_ledger_id'],

                    'financial_account_type' =>
                    $mapping['financial_account_type'],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | AUDIT TRAIL
            |--------------------------------------------------------------------------
            */

            $this->audit->trail(
                module: 'Account Product',
                action: 'Create',
                auditableType: AccountProduct::class,
                auditableId: $product->id,
                before: null,
                after: $product->fresh()->toArray(),
                description: "Account Product {$product->name} created"
            );

            return $product->load([
                'generalLedgerMappings.generalLedger'
            ]);
        });
    }

    private function generateProductCode(
        string $productType
    ): string {

        $prefix = match ($productType) {

            'savings' => 'SVG',

            'current' => 'CUR',

            'merchant' => 'MCH',

            'corporate' => 'COR',

            'domiciliary' => 'DOM',

            default => 'PRD',
        };

        do {

            $code = $prefix . mt_rand(100000, 999999);
        } while (
            AccountProduct::where('code', $code)->exists()
        );

        return $code;
    }
}
