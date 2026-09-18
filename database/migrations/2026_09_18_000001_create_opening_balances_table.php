<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('opening_balances', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 26);
            $table->string('branch_id', 26);
            $table->date('balance_date');
            $table->string('balance_type', 20); // cash, bank, forex
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('bank_account_id', 26)->nullable();
            $table->unsignedBigInteger('currency_variant_id')->nullable();
            $table->unsignedBigInteger('currency_denomination_id')->nullable();
            $table->decimal('quantity', 20, 4)->nullable();
            $table->decimal('rate', 20, 6)->nullable();
            $table->decimal('amount_rp', 24, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('finalized');
            $table->string('created_by', 26)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'balance_date']);
            $table->index(['tenant_id', 'branch_id', 'balance_type']);
            $table->index('bank_account_id');
            $table->index('currency_denomination_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_balances');
    }
};
