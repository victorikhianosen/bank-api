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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('customer_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->enum('type', [
                'residential',
                'office',
                'mailing'
            ])->default('residential');

            $table->text('address');
            $table->string('lga')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Nigeria');

            $table->json('old_addresses')->nullable();

            $table->boolean('is_primary')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
