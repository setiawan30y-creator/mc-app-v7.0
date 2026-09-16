<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mc_transaction_items', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('transaction_id');

            /*
             * Currency hierarchy:
             *
             * Currency
             *   -> Variant / Series
             *       -> Denomination
             */
            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            $table->foreignId('currency_variant_id')
                ->constrained('currency_variants')
                ->restrictOnDelete();

            $table->foreignId('currency_denomination_id')
                ->nullable()
                ->constrained('currency_denominations')
                ->restrictOnDelete();

            /*
             * Direction dari sisi Money Changer.
             *
             * buy  = Almara membeli currency dari customer
             * sell = Almara menjual currency ke customer
             */
            $table->string('direction', 10);

            /*
             * Quantity dapat berupa pecahan fisik.
             *
             * Contoh:
             * USD 100 x 5 = 500
             */
            $table->decimal('quantity', 20, 6);

            /*
             * Kurs yang digunakan saat transaksi.
             */
            $table->decimal('rate', 20, 6);

            /*
             * Snapshot kurs sebagai audit trail.
             */
            $table->unsignedBigInteger('rate_snapshot_id');

            /*
             * Nilai item.
             */
            $table->decimal('subtotal', 20, 6);

            /*
             * Threshold AML / internal limit.
             *
             * Untuk customer membeli foreign currency
             * dari Almara (direction = sell).
             */
            $table->decimal('usd_equivalent_amount', 20, 6)
                ->nullable();

            $table->decimal('threshold_rate', 20, 6)
                ->nullable();

            $table->unsignedBigInteger('threshold_rate_snapshot_id')
                ->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            /*
             * Foreign keys.
             */
            $table->foreign('transaction_id')
                ->references('id')
                ->on('mc_transactions')
                ->cascadeOnDelete();

            $table->foreign('rate_snapshot_id')
                ->references('id')
                ->on('rate_snapshots')
                ->restrictOnDelete();

            $table->foreign('threshold_rate_snapshot_id')
                ->references('id')
                ->on('rate_snapshots')
                ->restrictOnDelete();

            /*
             * Index.
             */
            $table->index(
                ['transaction_id', 'direction'],
                'mc_transaction_items_transaction_direction_index'
            );

            $table->index(
                [
                    'currency_id',
                    'currency_variant_id',
                    'currency_denomination_id'
                ],
                'mc_transaction_items_currency_index'
            );

            $table->index(
                ['rate_snapshot_id'],
                'mc_transaction_items_rate_snapshot_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mc_transaction_items');
    }
};