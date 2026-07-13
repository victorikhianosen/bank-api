<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {

            $table->id();

            $table->uuid('reference')
                ->index();

                $table->string('transaction_reference')
    ->index();

            $table->foreignId('general_ledger_id')
                ->constrained('general_ledgers')
                ->cascadeOnDelete();

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            $table->enum('entry_type', [
                'debit',
                'credit',
            ]);

            $table->decimal('amount', 25, 6);

            $table->decimal('balance_before', 25, 6)
                ->default(0);

            $table->decimal('balance_after', 25, 6)
                ->default(0);

            $table->string('narration')
                ->nullable();

            $table->timestamp('transaction_date');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};