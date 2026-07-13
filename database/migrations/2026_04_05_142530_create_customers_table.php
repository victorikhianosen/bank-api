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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_officer_id')->nullable()->constrained('account_officers')->nullOnDelete();
            $table->string('customer_number')->unique()->index();

            $table->string('referral_code')->unique()->nullable()->index();

            $table->string('customer_request')->nullable();

            $table->foreignId('guardian_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();

            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password')->nullable();
            $table->string('panic_password')->nullable();

            $table->string('pin')->nullable();

            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();

            $table->string('gender')->nullable();
            $table->string('dob')->nullable();

            $table->string('occupation')->nullable();
            $table->string('working_status')->nullable();
            $table->string('business_name')->nullable();

            $table->enum('status', [
                'pending',
                'active',
                'dormant',
                'frozen',
                'closed',
                'restricted'
            ])->default('pending');
            // $table->enum('status', ['pending', 'active', 'inactive', 'closed'])->default('pending');

            $table->string('bvn')->nullable()->unique();
            $table->string('nin_number')->nullable()->unique();
            $table->string('tin')->nullable()->unique();

            $table->boolean('is_staff')->default(false);
            $table->boolean('pep')->default(false);

            $table->boolean('enable_internet')->default(false);
            $table->boolean('enable_sms')->default(true);
            $table->boolean('enable_email')->default(true);
            $table->boolean('enable_reset_password')->default(false);
            $table->boolean('enable_panic_password')->default(false);

            $table->boolean('id_verified')->default(false);
            $table->boolean('face_verified')->default(false);
            $table->boolean('utility_verified')->default(false);

            $table->tinyInteger('tier')->default(1);
            $table->timestamp('tier_upgraded_at')->nullable();

            $table->string('mother_maiden_name')->nullable();
            $table->string('spouse_name')->nullable();

            $table->string('closure_reason')->nullable();
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

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
