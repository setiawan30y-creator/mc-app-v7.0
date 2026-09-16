<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mc_transaction_payments', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('transaction_id');
            $table->ulid('settlement_id');

            /*
             * Metode pembayaran:
             *
             * cash
             * transfer
             */
            $table->string('payment_method', 20);

            /*
             * Nominal pembayaran.
             */
            $table->decimal('amount', 20, 6);

            /*
             * Currency pembayaran.
             */
            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            /*
             * Hanya digunakan untuk transfer.
             */
            $table->foreignId('bank_account_id')
                ->nullable()
                ->constrained('bank_accounts')
                ->restrictOnDelete();

            /*
             * Bank mutation yang menjadi bukti
             * transfer masuk/keluar.
             */
            $table->ulid('bank_mutation_id')->nullable();

            /*
             * Identitas transfer dari sisi bank.
             */
            $table->string('transfer_reference', 150)->nullable();
            $table->string('transfer_external_id', 150)->nullable();

            $table->string('payer_name', 150)->nullable();

            /*
             * Status payment.
             *
             * pending
             * confirmed
             * failed
             */
            $table->string('payment_status', 30)
                ->default('pending');

            $table->dateTime('paid_at')->nullable();

            $table->dateTime('confirmed_at')->nullable();

            $table->unsignedBigInteger('confirmed_by')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            /*
             * Foreign keys.
             */
            $table->foreign('transaction_id')
                ->references('id')
                ->on('mc_transactions')
                ->cascadeOnDelete();

            $table->foreign('settlement_id')
                ->references('id')
                ->on('mc_transaction_settlements')
                ->cascadeOnDelete();

            $table->foreign('bank_mutation_id')
                ->references('id')
                ->on('bank_mutations')
                ->restrictOnDelete();

            $table->foreign('confirmed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            /*
             * Index.
             */
            $table->index(
                ['transaction_id', 'payment_status'],
                'mc_transaction_payments_transaction_status_index'
            );

            $table->index(
                ['settlement_id', 'payment_status'],
                'mc_transaction_payments_settlement_status_index'
            );

            $table->index(
                ['payment_method', 'payment_status'],
                'mc_transaction_payments_method_status_index'
            );

            $table->index(
                ['bank_account_id', 'paid_at'],
                'mc_transaction_payments_bank_account_date_index'
            );

            $table->index(
                ['bank_mutation_id'],
                'mc_transaction_payments_bank_mutation_index'
            );

            $table->index(
                ['transfer_external_id'],
                'mc_transaction_payments_transfer_external_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mc_transaction_payments');
    }
};