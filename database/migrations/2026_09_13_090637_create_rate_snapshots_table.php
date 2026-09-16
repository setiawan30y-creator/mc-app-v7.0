<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            $table->foreignId('rate_source_id')
                ->constrained('rate_sources')
                ->restrictOnDelete();

            $table->dateTime('effective_at');

            // Kurs acuan dari sumber eksternal
            $table->decimal('reference_rate', 20, 6);

            // Spread internal money changer
            $table->decimal('buy_spread', 20, 6)->default(0);
            $table->decimal('sell_spread', 20, 6)->default(0);

            // Hasil kurs transaksi
            $table->decimal('buy_rate', 20, 6);
            $table->decimal('sell_rate', 20, 6);

            $table->string('source_url', 500)->nullable();
            $table->string('notes', 500)->nullable();

            $table->boolean('is_active')->default(true);

            $table->foreignUlid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['tenant_id', 'currency_id', 'effective_at']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'rate_source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_snapshots');
    }
};