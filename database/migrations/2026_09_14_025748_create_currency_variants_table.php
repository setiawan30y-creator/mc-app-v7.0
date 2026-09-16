<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            $table->string('name', 100);
            $table->string('code', 50)->nullable();

            $table->text('description')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['currency_id', 'name']);

            $table->index(['currency_id', 'is_active']);
            $table->index(['currency_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_variants');
    }
};