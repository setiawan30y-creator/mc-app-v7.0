<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_inventory_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('branch_id');

            $table->ulid('inventory_id');

            /*
             * Sumber movement.
             */
            $table->ulid('transaction_id')->nullable();
            $table->ulid('cash_movement_id')->nullable();

            /*
             * in / out
             */
            $table->string('direction', 10);

            /*
             * Perubahan fisik.
             */
            $table->decimal('quantity', 20, 6);

            $table->decimal('amount', 20, 6);

            /*
             * Snapshot saldo setelah movement.
             *
             * Ini penting untuk audit trail.
             */
            $table->decimal('balance_quantity', 20, 6);

            $table->decimal('balance_amount', 20, 6);

            /*
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

            $table->foreign('inventory_id')
                ->references('id')
                ->on('cash_inventory')
                ->cascadeOnDelete();

            $table->foreign('transaction_id')
                ->references('id')
                ->on('mc_transactions')
                ->nullOnDelete();

            $table->foreign('cash_movement_id')
                ->references('id')
                ->on('cash_movements')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            /*
             * Index.
             */
            $table->index(
                ['inventory_id', 'created_at'],
                'cash_inventory_movements_inventory_date_index'
            );

            $table->index(
                ['transaction_id'],
                'cash_inventory_movements_transaction_index'
            );

            $table->index(
                ['cash_movement_id'],
                'cash_inventory_movements_cash_movement_index'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'created_at'],
                'cash_inventory_movements_scope_date_index'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'movement_type'],
                'cash_inventory_movements_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_inventory_movements');
    }
};