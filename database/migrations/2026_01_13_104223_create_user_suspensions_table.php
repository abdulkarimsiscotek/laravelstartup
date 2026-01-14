<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_suspensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('reason')->nullable();
            $table->timestamp('suspended_at')->useCurrent();
            $table->timestamp('suspended_until')->nullable();

            // who suspended (optional)
            $table->foreignId('suspended_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'suspended_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_suspensions');
    }
};