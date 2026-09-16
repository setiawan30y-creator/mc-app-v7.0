<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_mutations', function (Blueprint $table) {
            $table->ulid('id')->primary();

            /*
             * Tenant & branch isolation.
             */
            $table->ulid('tenant_id');
            $table->ulid('branch_id');

            /*
             * Rekening sumber mutasi.
             */
            $table->ulid('bank_account_id');

            /*
             * Tanggal transaksi dan value date.
             */
            $table->dateTime('transaction_date');
            $table->date('value_date')->nullable();

            /*
             * Informasi mutasi dari bank.
             */
            $table->string('reference', 150)->nullable();
            $table->text('description')->nullable();

            /*
             * Nilai mutasi.
             *
             * Debit  = uang keluar.
             * Credit = uang masuk.
             *
             * Hanya salah satu yang seharusnya terisi
             * untuk satu mutasi.
             */
            $table->decimal('debit', 20, 2)->default(0);
            $table->decimal('credit', 20, 2)->default(0);

            /*
             * Saldo sebagaimana tercatat pada rekening/bank statement.
             */
            $table->decimal('balance', 20, 2)->nullable();

            /*
             * ID dari sumber eksternal.
             *
             * Berguna untuk mencegah mutasi hasil import/API
             * masuk dua kali.
             */
            $table->string('external_id', 150)->nullable();

            /*
             * Sumber data mutasi.
             *
             * manual
             * import
             * api
             * bank_statement
             */
            $table->string('source', 30)->default('manual');

            /*
             * Status rekonsiliasi.
             *
             * unmatched
             * matched
             * manual
             * ignored
             */
            $table->string('reconciliation_status', 30)
                ->default('unmatched');

            /*
             * Untuk sementara menggunakan ULID reference.
             *
             * MC transaction table belum dibuat.
             * Nanti dapat dikonversi menjadi foreign key
             * setelah modul transaksi tersedia.
             */
            $table->ulid('matched_transaction_id')->nullable();

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

            $table->foreign('bank_account_id')
                ->references('id')
                ->on('bank_accounts')
                ->cascadeOnDelete();

            /*
             * Indexes.
             */
            $table->index(
                ['tenant_id', 'branch_id', 'transaction_date'],
                'bank_mutations_tenant_branch_date_idx'
            );

            $table->index(
                ['bank_account_id', 'transaction_date'],
                'bank_mutations_account_date_idx'
            );

            $table->index(
                ['tenant_id', 'reconciliation_status'],
                'bank_mutations_reconciliation_idx'
            );

            $table->index(
                ['tenant_id', 'source'],
                'bank_mutations_source_idx'
            );

            $table->index(
                ['tenant_id', 'external_id'],
                'bank_mutations_external_id_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_mutations');
    }
};