<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mc_transaction_settlements', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('transaction_id');

            /*
             * Arah settlement dari perspektif customer.
             *
             * customer_receives
             * customer_pays
             */
            $table->string('direction', 30);

            /*
             * Currency settlement.
             *
             * Bisa berbeda dengan currency item.
             * Contoh:
             *
             * Item:
             * USD 1,000
             *
             * Customer membayar:
             * IDR 15,500,000
             */
            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            $table->decimal('amount', 20, 6);

            /*
             * Status settlement dapat dihitung dari payment,
             * tetapi field ini disediakan untuk operational state.
             */
            $table->string('status', 30)
                ->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('transaction_id')
                ->references('id')
                ->on('mc_transactions')
                ->cascadeOnDelete();

            $table->index(
                ['transaction_id', 'direction'],
                'mc_transaction_settlements_transaction_direction_index'
            );

            $table->index(
                ['transaction_id', 'status'],
                'mc_transaction_settlements_transaction_status_index'
            );

            $table->index(
                ['currency_id'],
                'mc_transaction_settlements_currency_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mc_transaction_settlements');
    }
};