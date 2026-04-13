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

        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            $table->foreignId('currency_id')->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            $table->string('name');
            $table->string('code')->unique();

            $table->enum('type', [
                'asset',
                'liability',
                'equity',
                'income',
                'expense'
            ]);

            $table->string('purpose')->nullable();

            $table->foreignId('parent_id')->nullable()
                ->constrained('ledger_accounts')
                ->nullOnDelete();

            $table->decimal('balance', 25, 2)->default(0);

            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};
