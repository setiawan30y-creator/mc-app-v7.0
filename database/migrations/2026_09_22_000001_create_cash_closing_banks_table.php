<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_closing_banks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('cash_closing_id');
            $table->ulid('bank_account_id');
            $table->decimal('system_amount', 20, 2)->default(0);
            $table->decimal('physical_amount', 20, 2)->default(0);
            $table->decimal('difference_amount', 20, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('cash_closing_id')
                ->references('id')->on('cash_closings')
                ->cascadeOnDelete();

            $table->foreign('bank_account_id')
                ->references('id')->on('bank_accounts')
                ->restrictOnDelete();

            $table->unique(['cash_closing_id', 'bank_account_id']);
            $table->index(['bank_account_id', 'cash_closing_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_closing_banks');
    }
};
