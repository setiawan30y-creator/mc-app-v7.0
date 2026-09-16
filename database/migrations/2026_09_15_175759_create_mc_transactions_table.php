<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mc_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            /*
             * Scope organisasi.
             */
            $table->ulid('tenant_id');
            $table->ulid('branch_id');

            /*
             * Identitas transaksi.
             */
            $table->string('transaction_no', 50);

            $table->dateTime('transaction_date');

            /*
             * Customer tetap menjadi pihak transaksi.
             */
            $table->ulid('customer_id');

            /*
             * Status utama transaksi.
             *
             * draft
             * pending_payment
             * paid
             * completed
             * cancelled
             * rejected
             */
            $table->string('status', 30)->default('draft');

            /*
             * Status settlement.
             *
             * pending
             * partial
             * settled
             * failed
             */
            $table->string('settlement_status', 30)
                ->default('pending');

            /*
             * Source of funds.
             */
            $table->string('fund_source_type', 50)->nullable();
            $table->text('fund_source_detail')->nullable();

            /*
             * Purpose transaksi.
             */
            $table->string('transaction_purpose_type', 50)->nullable();
            $table->text('transaction_purpose_detail')->nullable();

            /*
             * Pickup.
             *
             * Customer tetap menjadi pihak utama.
             * Jika pickup berbeda, data snapshot penerima
             * disimpan di transaksi.
             */
            $table->boolean('pickup_same_as_customer')->default(true);

            $table->string('pickup_party_type', 50)->nullable();
            $table->string('pickup_party_id', 100)->nullable();

            $table->string('pickup_name', 150)->nullable();
            $table->string('pickup_phone', 50)->nullable();
            $table->string('pickup_identity_type', 30)->nullable();
            $table->string('pickup_identity_number', 100)->nullable();
            $table->string('pickup_relationship', 100)->nullable();

            $table->string('pickup_status', 30)
                ->default('not_required');

            $table->dateTime('pickup_at')->nullable();

            /*
             * User yang menyerahkan uang/barang.
             */
            $table->unsignedBigInteger('handed_over_by')->nullable();

            /*
             * WhatsApp receipt.
             */
            $table->boolean('send_wa_receipt')->default(false);

            $table->unsignedBigInteger('wa_template_id')->nullable();
            $table->string('wa_template_version', 50)->nullable();

            $table->string('wa_status', 30)
                ->default('pending');

            $table->dateTime('wa_sent_at')->nullable();
            $table->string('wa_message_id', 150)->nullable();
            $table->text('wa_error')->nullable();

            /*
             * Audit.
             */
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->text('notes')->nullable();

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

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->restrictOnDelete();

            $table->foreign('handed_over_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            /*
             * Transaction number unik dalam tenant.
             */
            $table->unique(
                ['tenant_id', 'transaction_no'],
                'mc_transactions_tenant_transaction_no_unique'
            );

            /*
             * Operational indexes.
             */
            $table->index(
                ['tenant_id', 'branch_id', 'transaction_date'],
                'mc_transactions_scope_date_index'
            );

            $table->index(
                ['tenant_id', 'customer_id', 'transaction_date'],
                'mc_transactions_customer_date_index'
            );

            $table->index(
                ['tenant_id', 'status'],
                'mc_transactions_status_index'
            );

            $table->index(
                ['tenant_id', 'settlement_status'],
                'mc_transactions_settlement_status_index'
            );

            $table->index(
                ['tenant_id', 'wa_status'],
                'mc_transactions_wa_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mc_transactions');
    }
};