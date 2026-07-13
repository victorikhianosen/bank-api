<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('financial_transactions', function (Blueprint $table) {

            $table->id();

            // Shared business transaction reference

            $table->string('transaction_reference')
                ->index();

            // Unique row reference

            $table->string('reference_no')
                ->unique();

            // Business transaction type

            $table->string('transaction_type')
                ->index();

            // Source account number

            $table->string('source_account_no')
                ->nullable()
                ->index();

            // Destination account number

            $table->string('destination_account_no')
                ->nullable()
                ->index();

            // Amount

            $table->decimal('amount', 25, 6);

            // Narration

            $table->string('narration')
                ->nullable();

            // Request channel

            $table->string('channel')
                ->nullable();

            // Transaction status

            $table->string('status')
                ->default('pending');

            // Reversal relationship

            $table->foreignId('reversal_of')
                ->nullable()
                ->constrained('financial_transactions')
                ->nullOnDelete();

            // External provider reference

            $table->string('external_reference')
                ->nullable();

            // Reversal tracking

            $table->boolean('is_reversed')
                ->default(false);

            $table->timestamp('reversed_at')
                ->nullable();

            // Audit

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('processed_at')
                ->nullable();

            $table->timestamps();

            // Indexes

            $table->index('transaction_reference');

            $table->index('reference_no');

            $table->index('transaction_type');

            $table->index('source_account_no');

            $table->index('destination_account_no');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
