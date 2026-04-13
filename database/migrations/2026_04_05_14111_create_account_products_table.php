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
        Schema::create('account_products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('account_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->unique();

            $table->decimal('interest_rate', 18, 2)->default(0.00);
            $table->enum('interest_type', ['flat', 'daily', 'tiered'])->default('flat');
            $table->enum('interest_posting', ['daily', 'monthly', 'quarterly', 'annually'])->default('monthly');

            $table->decimal('min_balance', 18, 2)->default(0.00);
            $table->decimal('max_balance', 18, 2)->nullable();

            $table->boolean('overdraft_allowed')->default(false);
            $table->decimal('overdraft_limit', 18, 2)->nullable();

            $table->decimal('daily_withdrawal_limit', 18, 2)->nullable();
            $table->decimal('max_transactions_per_day', 18, 2)->nullable();
            $table->decimal('max_per_single_transaction', 18, 2)->nullable();

            $table->decimal('transfer_fee', 10, 2)->default(0.00);
            $table->decimal('maintenance_fee', 10, 2)->default(0.00);

            $table->decimal('dormancy_days')->default(365);

            $table->string('created_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');

            $table->index('account_category_id');
            $table->index('currency_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_products');
    }
};
