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

        Schema::create('account_product_gl_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_product_id')->constrained('account_products')->cascadeOnDelete();
            $table->foreignId('general_ledger_id')->constrained('general_ledgers')->cascadeOnDelete();
            $table->smallInteger('financial_account_type');
            $table->timestamps();
            $table->unique(['account_product_id', 'financial_account_type'], 'account_product_financial_account_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_product_gl_mappings');
    }
};
