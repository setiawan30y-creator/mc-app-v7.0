<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_denominations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('currency_variant_id')
                ->constrained('currency_variants')
                ->restrictOnDelete();

            $table->decimal('value', 20, 6);
            $table->string('label', 50)->nullable();

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['currency_variant_id', 'value']);

            $table->index([
                'currency_variant_id',
                'is_active',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_denominations');
    }
};