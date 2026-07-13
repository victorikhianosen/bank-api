<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiers', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->tinyInteger('level')->unique();

            $table->text('description')->nullable();

            $table->decimal('daily_transaction_limit', 19, 6)->nullable();

            $table->decimal('daily_withdrawal_limit', 19, 6)->nullable();

            $table->decimal('daily_transfer_limit', 19, 6)->nullable();

            $table->decimal('max_account_balance', 19, 6)->nullable();

            $table->decimal('max_single_transaction_amount', 19, 6)->nullable();

            $table->decimal('max_daily_inflow', 19, 6)->nullable();

            $table->decimal('max_daily_outflow', 19, 6)->nullable();

            $table->boolean('require_bvn')->default(false);

            $table->boolean('require_nin')->default(false);

            $table->boolean('require_address_verification')->default(false);

            $table->boolean('require_face_verification')->default(false);

            $table->enum('status', [
                'active',
                'inactive',
                'disabled',
                'pending'
            ])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};