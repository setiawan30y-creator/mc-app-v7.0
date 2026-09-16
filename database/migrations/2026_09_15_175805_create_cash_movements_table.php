<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('branch_id');

            /*
             * Relasi transaksi/payment.
             *
             * Nullable karena tidak semua cash movement
             * berasal dari transaksi customer.
             *
             * Contoh:
             * - transaction : berasal dari transaksi MC
             * - adjustment  : penyesuaian kas
             * - opening     : saldo awal
             * - closing     : penyesuaian closing
             * - transfer    : perpindahan kas
             */
            $table->ulid('transaction_id')->nullable();
            $table->ulid('payment_id')->nullable();

            /*
             * Currency fisik.
             */
            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            $table->foreignId('currency_variant_id')
                ->constrained('currency_variants')
                ->restrictOnDelete();

            /*
             * Cash harus dapat ditelusuri sampai denomination.
             */
            $table->foreignId('currency_denomination_id')
                ->constrained('currency_denominations')
                ->restrictOnDelete();

            /*
             * Direction:
             *
             * in  = cash masuk ke Almara
             * out = cash keluar dari Almara
             */
            $table->string('direction', 10);

            /*
             * Jumlah lembar/koin.
             */
            $table->decimal('quantity', 20, 6);

            /*
             * Nilai total fisik.
             */
            $table->decimal('amount', 20, 6);

            /*
             * Jenis movement.
             *
             * transaction
             * adjustment
             * opening
             * closing
             * transfer
             */
            $table->string('movement_type', 30);

            $table->string('reference', 150)->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

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

            $table->foreign('transaction_id')
                ->references('id')
                ->on('mc_transactions')
                ->nullOnDelete();

            $table->foreign('payment_id')
                ->references('id')
                ->on('mc_transaction_payments')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            /*
             * Index operasional.
             */
            $table->index(
                ['tenant_id', 'branch_id', 'created_at'],
                'cash_movements_scope_date_index'
            );

            $table->index(
                ['transaction_id'],
                'cash_movements_transaction_index'
            );

            $table->index(
                ['payment_id'],
                'cash_movements_payment_index'
            );

            $table->index(
                [
                    'tenant_id',
                    'branch_id',
                    'currency_id',
                    'currency_variant_id',
                    'currency_denomination_id'
                ],
                'cash_movements_inventory_index'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'movement_type'],
                'cash_movements_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};