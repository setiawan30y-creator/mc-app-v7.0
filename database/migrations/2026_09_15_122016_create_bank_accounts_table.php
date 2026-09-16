<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();

            /*
             * Tenant & branch isolation.
             */
            $table->ulid('tenant_id');
            $table->ulid('branch_id');

            /*
             * Informasi bank.
             */
            $table->string('bank_name', 100);
            $table->string('bank_code', 30)->nullable();

            /*
             * Informasi rekening.
             */
            $table->string('account_name', 150);
            $table->string('account_number', 100);

            /*
             * Mata uang rekening.
             */
            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            /*
             * Saldo awal rekening.
             *
             * current_balance TIDAK disimpan di sini.
             * Saldo berjalan akan dihitung dari opening_balance
             * + mutasi bank.
             */
            $table->decimal('opening_balance', 20, 2)->default(0);

            /*
             * Status rekening.
             */
            $table->boolean('is_active')->default(true);

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

            /*
             * Indexes.
             */
            $table->index(
                ['tenant_id', 'branch_id', 'is_active'],
                'bank_accounts_tenant_branch_active_idx'
            );

            $table->index(
                ['tenant_id', 'bank_name'],
                'bank_accounts_tenant_bank_idx'
            );

            /*
             * Satu nomor rekening dapat digunakan pada
             * beberapa tenant berbeda, tetapi tidak boleh
             * duplikat dalam tenant yang sama.
             */
            $table->unique(
                ['tenant_id', 'account_number'],
                'bank_accounts_tenant_account_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};