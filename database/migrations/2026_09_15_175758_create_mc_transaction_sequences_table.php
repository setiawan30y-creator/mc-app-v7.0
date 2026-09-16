<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mc_transaction_sequences', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('branch_id');

            $table->date('sequence_date');

            /*
             * Nomor terakhir yang berhasil digunakan
             * pada tenant + branch + tanggal tersebut.
             */
            $table->unsignedInteger('last_number')->default(0);

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->cascadeOnDelete();

            $table->unique(
                ['tenant_id', 'branch_id', 'sequence_date'],
                'mc_transaction_sequences_scope_date_unique'
            );

            $table->index(
                ['tenant_id', 'branch_id'],
                'mc_transaction_sequences_scope_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mc_transaction_sequences');
    }
};