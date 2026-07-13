<?php

namespace App\Services;

use App\Helpers\TransactionReference;
use App\Models\Account;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InternalTransferService
{
    public function __construct(
        private LedgerService $ledgerService
    ) {}

    public function transfer(
        array $data
    ): FinancialTransaction {

        return DB::transaction(function () use ($data) {

            $sourceAccount = Account::with([
                'product.generalLedgerMappings.generalLedger'
            ])->where(
                'account_no',
                $data['source_account_no']
            )->lockForUpdate()->firstOrFail();

            $destinationAccount = Account::with([
                'product.generalLedgerMappings.generalLedger'
            ])->where(
                'account_no',
                $data['destination_account_no']
            )->lockForUpdate()->firstOrFail();

            // Prevent same account transfer

            if (
                $sourceAccount->id ===
                $destinationAccount->id
            ) {

                throw ValidationException::withMessages([
                    'destination_account_no' => [
                        'Cannot transfer to same account'
                    ]
                ]);
            }

            // Validate balance

            if (
                $sourceAccount->available_balance <
                $data['amount']
            ) {

                throw ValidationException::withMessages([
                    'amount' => [
                        'Insufficient balance'
                    ]
                ]);
            }

            // Validate currency

            if (
                $sourceAccount->currency_id !=
                $destinationAccount->currency_id
            ) {

                throw ValidationException::withMessages([
                    'currency' => [
                        'Currency mismatch'
                    ]
                ]);
            }

            // Shared transaction reference

            $transactionReference =
                TransactionReference::generate('TRF');

            // Create financial transaction

            $financialTransaction =
                FinancialTransaction::create([

                    'transaction_reference' =>
                        $transactionReference,

                    'reference' =>
                        TransactionReference::generate(
                            'FIN'
                        ),

                    'source_account_id' =>
                        $sourceAccount->id,

                    'destination_account_id' =>
                        $destinationAccount->id,

                    'amount' =>
                        $data['amount'],

                    'narration' =>
                        $data['narration'] ?? null,

                    'status' => 'pending',

                    'created_by' =>
                        Auth::id(),
                ]);

            // Debit source account

            $debitTransaction =
                $this->ledgerService->postTransaction(

                    account: $sourceAccount,

                    transaction_type: 'wallet_transfer',

                    type: 'debit',

                    direction: 'Money Left',

                    amount: $data['amount'],

                    narration:
                        'Transfer to ' .
                        $destinationAccount->account_no,

                    transactionReference:
                        $transactionReference,

                    financialTransactionId:
                        $financialTransaction->id,
                        channel: $data['channel']
                );

            // Credit destination account

            $creditTransaction =
                $this->ledgerService->postTransaction(

                    account: $destinationAccount,

                    transaction_type: 'wallet_transfer',

                    type: 'credit',

                    direction: 'Move Enter',

                    amount: $data['amount'],

                    narration: $data['narration'] ??  'Transfer from ' .
                        $sourceAccount->account_no,
                       

                    transactionReference:
                        $transactionReference,

                    financialTransactionId:
                        $financialTransaction->id,
                                                channel: $data['channel']

                );

            // Sender GL

            $senderGl =
                $sourceAccount->product
                    ->generalLedgerMappings()
                    ->where(
                        'financial_account_type',
                        1
                    )->first()
                    ?->generalLedger;

            // Receiver GL

            $receiverGl =
                $destinationAccount->product
                    ->generalLedgerMappings()
                    ->where(
                        'financial_account_type',
                        1
                    )->first()
                    ?->generalLedger;

            if (! $senderGl || ! $receiverGl) {

                throw ValidationException::withMessages([
                    'ledger' => [
                        'GL mapping not found'
                    ]
                ]);
            }

            // Debit sender GL

            $this->ledgerService->postJournal(

                ledger: $senderGl,

                transaction: $debitTransaction,

                entryType: 'debit',

                amount: $data['amount'],

                narration:
                    'Internal transfer debit',

                transactionReference:
                    $transactionReference,

            );

            // Credit receiver GL

            $this->ledgerService->postJournal(

                ledger: $receiverGl,

                transaction: $creditTransaction,

                entryType: 'credit',

                amount: $data['amount'],

                narration:
                    'Internal transfer credit',

                transactionReference:
                    $transactionReference
            );

            // Complete transaction

            $financialTransaction->update([

                'status' => 'successful',

                'processed_at' => now(),
            ]);

            return $financialTransaction->load([
                'sourceAccount',
                'destinationAccount'
            ]);
        });
    }
}