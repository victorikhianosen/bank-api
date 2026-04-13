<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('account_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies');

            $table->string('account_number')->unique();
            $table->string('account_name')->nullable();

            $table->decimal('ledger_balance', 25, 2)->default(0);
            $table->decimal('available_balance', 25, 2)->default(0);

            $table->decimal('blocked_balance', 25, 2)->default(0);

            $table->tinyInteger('tier')->default(1)->index();

            $table->enum('status', [
                'pending',
                'active',
                'dormant',
                'frozen',
                'closed',
                'restricted'
            ])->default('pending');

            $table->boolean('pnd')->default(false);
            $table->boolean('pnc')->default(false);

            $table->timestamp('last_transaction_at')->nullable();

            $table->nullableUlidMorphs('created_by');
            $table->nullableUlidMorphs('approved_by');
            $table->nullableUlidMorphs('updated_by');
            $table->nullableUlidMorphs('closed_by');

            $table->nullableUlidMorphs('rejected_by');
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
