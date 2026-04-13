<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('product_gl_mappings', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('product_id')
                ->constrained('account_products')
                ->cascadeOnDelete();

            $table->string('gl_role');

            $table->foreignId('ledger_account_id')
                ->constrained('ledger_accounts')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['product_id', 'gl_role']);

            // $table->unique(['ledger_account_id', 'gl_role']);

            $table->index('product_id');
            $table->index('ledger_account_id');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_gl_mappings');
    }
};
