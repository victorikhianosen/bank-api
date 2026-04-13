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
        Schema::create('account_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();

            $table->foreignUlid('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignUlid('account_product_id')->constrained();
            $table->foreignId('currency_id')->constrained('currencies');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->nullableUlidMorphs('created_by');
            $table->nullableUlidMorphs('approved_by');
            $table->nullableUlidMorphs('rejected_by');

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->string('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_requests');
    }
};
