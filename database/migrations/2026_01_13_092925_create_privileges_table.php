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
        Schema::create(config('rbac.tables.privileges', 'privileges'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // users.read, roles.manage, etc.
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('rbac.tables.privileges', 'privileges'));
    }
};
