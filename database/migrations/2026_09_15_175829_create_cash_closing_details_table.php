<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_closing_details', function (Blueprint $table) {
            $table->ulid('id');

            $table->ulid('closing_id');

            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('currency_variant_id');
            $table->unsignedBigInteger('currency_denomination_id');

            /*
             * Snapshot kondisi sistem ketika closing dibuat.
             */
            $table->decimal('system_quantity', 20, 6)->default(0);
            $table->decimal('system_amount', 20, 6)->default(0);

            /*
             * Hasil penghitungan fisik.
             */
            $table->decimal('physical_quantity', 20, 6)->default(0);
            $table->decimal('physical_amount', 20, 6)->default(0);

            /*
             * Selisih:
             * physical - system
             */
            $table->decimal('difference_quantity', 20, 6)->default(0);
            $table->decimal('difference_amount', 20, 6)->default(0);

            /*
             * pending
             * approved
             * applied
             * rejected
             */
            $table->string('adjustment_status', 30)->default('pending');

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();

            /*
             * Jika selisih akhirnya dibuat sebagai
             * inventory adjustment, movement-nya ditautkan di sini.
             */
            $table->ulid('adjustment_movement_id')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('closing_id')
                ->references('id')
                ->on('cash_closings')
                ->cascadeOnDelete();

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->restrictOnDelete();

            $table->foreign('currency_variant_id')
                ->references('id')
                ->on('currency_variants')
                ->restrictOnDelete();

            $table->foreign('currency_denomination_id')
                ->references('id')
                ->on('currency_denominations')
                ->restrictOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('adjustment_movement_id')
                ->references('id')
                ->on('cash_inventory_movements')
                ->nullOnDelete();

            // Satu denomination hanya satu baris per closing
            $table->unique(
                ['closing_id', 'currency_denomination_id'],
                'cash_closing_details_closing_denomination_unique'
            );

            // Indexes
            $table->index(
                ['closing_id', 'currency_id'],
                'cash_closing_details_currency_index'
            );

            $table->index(
                ['currency_variant_id', 'currency_denomination_id'],
                'cash_closing_details_variant_denomination_index'
            );

            $table->index(
                ['closing_id', 'adjustment_status'],
                'cash_closing_details_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_closing_details');
    }
};