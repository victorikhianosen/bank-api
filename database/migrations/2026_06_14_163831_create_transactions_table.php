<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {

            $table->id();

            // Shared transaction reference

            $table->string('transaction_reference')
                ->index();

            // Unique posting reference

            $table->string('reference_no')
                ->unique();

            // Business transaction type

            $table->string('transaction_type')
                ->index();

            // Accounting type

            $table->enum('type', [
                'debit',
                'credit',
            ]);

            // Posting account

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->cascadeOnDelete();

            // Source account number

            $table->string('source_account_no')
                ->nullable()
                ->index();

            // Destination account number

            $table->string('destination_account_no')
                ->nullable()
                ->index();

            // Customer

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            // Currency

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            // Parent financial transaction

            $table->foreignId('financial_transaction_id')
                ->nullable()
                ->constrained('financial_transactions')
                ->nullOnDelete();

            // Transaction amount

            $table->decimal('amount', 25, 6);

            // Account balances

            $table->decimal('balance_before', 25, 6)
                ->default(0);

            $table->decimal('balance_after', 25, 6)
                ->default(0);

            // Narration

            $table->string('narration')
                ->nullable();

            // Request channel

            $table->string('channel')
                ->nullable();

            // External provider reference

            $table->string('external_reference')
                ->nullable();

            // Transaction status

            $table->string('status')
                ->default('pending');

            // Original request payload

            // Extra response/provider data

            $table->json('metadata')
                ->nullable();

            $table->json('payload')
                ->nullable();

                $table->string('direction')
    ->nullable();

            // Reversal tracking

            $table->boolean('is_reversed')
                ->default(false);

            $table->timestamp('reversed_at')
                ->nullable();

            $table->foreignId('reversed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Audit

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('transaction_date')
                ->nullable();

            $table->timestamps();

            // Indexes

            $table->index('transaction_reference');

            $table->index('reference_no');

            $table->index('transaction_type');

            $table->index('account_id');

            $table->index('source_account_no');

            $table->index('destination_account_no');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
}