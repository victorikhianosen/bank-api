<?php

namespace App\Services;

use App\Helpers\TransactionReference;
use App\Models\Account;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class LedgerService
{
    public function postTransaction(
        Account $account,
        string $transaction_type,
        string $type,
        string $direction,
        float $amount,
        string $narration,
        string $transactionReference,
        ?int $financialTransactionId = null,
        string $channel,
    ): Transaction {

        $account = Account::lockForUpdate()
            ->findOrFail($account->id);

        $beforeBalance =
            $account->available_balance;

        $afterBalance =
            $direction === 'debit'
            ? $beforeBalance - $amount
            : $beforeBalance + $amount;

        $account->update([

            'account_balance' =>
                $afterBalance,

            'available_balance' =>
                $afterBalance,
        ]);

        return Transaction::create([

            'transaction_reference' =>
                $transactionReference,

            'reference' =>
                TransactionReference::generate(
                    'WAL'
                ),

            'account_id' =>
                $account->id,

            'customer_id' =>
                $account->customer_id,

            'currency_id' =>
                $account->currency_id,

            'financial_transaction_id' =>
                $financialTransactionId,

                    'transaction_type' =>
                $transaction_type,


            'type' =>
                $type,

            'direction' =>
                $direction,

            'amount' =>
                $amount,

            'balance_before' =>
                $beforeBalance,

            'balance_after' =>
                $afterBalance,

            'narration' =>
                $narration,

            'channel' =>
                $channel,

            'transaction_date' =>
                now(),

            'created_by' =>
                Auth::id(),
        ]);
    }

    public function postJournal(
        GeneralLedger $ledger,
        Transaction $transaction,
        string $entryType,
        float $amount,
        string $narration,
        string $transactionReference
    ): JournalEntry {

        $ledger = GeneralLedger::lockForUpdate()
            ->findOrFail($ledger->id);

        $beforeBalance =
            $ledger->balance;

        $increaseOnDebit = in_array(
            $ledger->classification,
            ['asset', 'expense']
        );

        if ($entryType === 'debit') {

            $afterBalance = $increaseOnDebit
                ? $beforeBalance + $amount
                : $beforeBalance - $amount;

        } else {

            $afterBalance = $increaseOnDebit
                ? $beforeBalance - $amount
                : $beforeBalance + $amount;
        }

        $ledger->update([
            'balance' => $afterBalance
        ]);

        return JournalEntry::create([

            'transaction_reference' =>
                $transactionReference,

            'reference' =>
                TransactionReference::generate(
                    'JRN'
                ),

            'general_ledger_id' =>
                $ledger->id,

            'account_id' =>
                $transaction->account_id,

            'transaction_id' =>
                $transaction->id,

            'currency_id' =>
                $transaction->currency_id,

            'entry_type' =>
                $entryType,

            'amount' =>
                $amount,

            'balance_before' =>
                $beforeBalance,

            'balance_after' =>
                $afterBalance,

            'narration' =>
                $narration,

            'transaction_date' =>
                now(),

            'created_by' =>
                Auth::id(),
        ]);
    }
}