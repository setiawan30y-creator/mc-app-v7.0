<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forex_stocks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id');
            $table->ulid('branch_id');
            $table->ulid('currency_variant_id');
            $table->ulid('currency_denomination_id');
            $table->decimal('quantity', 18, 6)->default(0);
            $table->date('stock_date');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('currency_variant_id')->references('id')->on('currency_variants')->cascadeOnDelete();
            $table->foreign('currency_denomination_id')->references('id')->on('currency_denominations')->cascadeOnDelete();

            $table->unique(
                ['tenant_id', 'branch_id', 'currency_denomination_id', 'stock_date'],
                'forex_stocks_daily_unique'
            );
            $table->index(['tenant_id', 'branch_id', 'stock_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forex_stocks');
    }
};
