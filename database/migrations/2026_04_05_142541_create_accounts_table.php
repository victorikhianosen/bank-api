<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('account_product_id')
                ->constrained('account_products')
                ->restrictOnDelete();


            $table->string('account_no', 20)
                ->unique();

            $table->string('account_name');

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->string('customer_number')
                ->nullable()
                ->index();



            $table->foreignId('account_officer_id')
                ->nullable()
                ->constrained('account_officers')
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            $table->foreignId('tier_id')
                ->nullable()
                ->constrained('tiers');

            $table->enum('status', [
                'submitted',
                'pending',
                'approved',
                'active',
                'rejected',
                'withdrawn',
                'closed',
                'dormant',
                'frozen',
                'restricted',
            ])->default('pending');

            $table->string('sub_status')
                ->nullable();

            $table->boolean('pnd')->default(false);
            $table->boolean('pnc')->default(false);

            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('withdrawn_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();

            $table->string('channel')->nullable();

            $table->decimal('account_balance', 19, 6)
                ->default(0);

            $table->decimal('available_balance', 19, 6)
                ->default(0);

            $table->decimal('on_hold_funds', 19, 6)
                ->default(0);

            $table->decimal('total_deposits', 19, 6)
                ->default(0);

            $table->decimal('total_withdrawals', 19, 6)
                ->default(0);

            $table->decimal('total_interest_earned', 19, 6)
                ->default(0);

            $table->decimal('total_interest_posted', 19, 6)
                ->default(0);

            $table->decimal('total_fees_charge', 19, 6)
                ->default(0);

            $table->decimal('total_penalty_charge', 19, 6)
                ->default(0);

            $table->decimal('total_withhold_tax', 19, 6)
                ->default(0);

            $table->date('last_interest_calculation_date')
                ->nullable();

            $table->date('interest_posted_till_date')
                ->nullable();

            $table->string('reason_for_block', 255)
                ->nullable();

            $table->timestamps();
            $table->index('account_no');

            $table->index('customer_id');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
