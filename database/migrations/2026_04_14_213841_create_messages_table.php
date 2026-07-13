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

        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('channel', ['email', 'sms', 'whatsapp', 'telegram', 'push']);

            $table->string('recipient');

            $table->string('subject')->nullable();
            $table->longText('message');

            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();

            $table->integer('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();

            $table->string('provider')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['status', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
