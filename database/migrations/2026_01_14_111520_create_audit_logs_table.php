<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Who did it (nullable for system actions)
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // What happened
            $table->string('action', 191);

            // What was targeted
            $table->string('target_type', 191)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();

            // Extra info (JSON)
            $table->json('meta')->nullable();

            // Request context
            $table->string('ip', 45)->nullable(); // supports IPv4/IPv6
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Useful indexes for querying
            $table->index(['action']);
            $table->index(['target_type', 'target_id']);
            $table->index(['actor_user_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};