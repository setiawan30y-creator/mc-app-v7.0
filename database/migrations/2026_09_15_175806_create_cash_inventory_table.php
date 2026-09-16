<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_inventory', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('branch_id');

            /*
             * Hierarki uang fisik.
             */
            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            $table->foreignId('currency_variant_id')
                ->constrained('currency_variants')
                ->restrictOnDelete();

            $table->foreignId('currency_denomination_id')
                ->constrained('currency_denominations')
                ->restrictOnDelete();

            /*
             * Saldo fisik saat ini.
             *
             * Quantity = jumlah lembar/koin.
             * Total amount = nilai nominal.
             */
            $table->decimal('quantity', 20, 6)->default(0);

            $table->decimal('total_amount', 20, 6)->default(0);

            $table->string('status', 20)->default('active');

            $table->timestamps();

            /*
             * Foreign keys.
             */
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->cascadeOnDelete();

            /*
             * Satu denomination hanya mempunyai satu
             * inventory record per tenant + branch.
             */
            $table->unique(
                [
                    'tenant_id',
                    'branch_id',
                    'currency_denomination_id'
                ],
                'cash_inventory_scope_denomination_unique'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'status'],
                'cash_inventory_scope_status_index'
            );

            $table->index(
                ['currency_id', 'currency_variant_id'],
                'cash_inventory_currency_variant_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_inventory');
    }
};