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
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->string('performed_by_type')->nullable();
            $table->string('performed_by_id')->nullable();

            $table->string('auditable_type')->nullable();
            $table->string('auditable_id')->nullable();

            $table->string('module');
            $table->string('actions');
            $table->json('before_change')->nullable();
            $table->json('after_change')->nullable();
            $table->longText('description')->nullable();
            $table->string('ip')->nullable();
            $table->string('agent')->nullable();

            $table->string('channel')->nullable();
            $table->index('branch_id');
            $table->index(['performed_by_type', 'performed_by_id']);
            $table->index('module');
            $table->index('actions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
