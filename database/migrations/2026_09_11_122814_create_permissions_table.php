<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('name');
            $table->string('code', 100)->unique();
            $table->string('module', 50);
            $table->string('description')->nullable();

            $table->boolean('is_system')->default(true);
            $table->string('status', 20)->default('active');

            $table->timestamps();

            $table->index(['module', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};