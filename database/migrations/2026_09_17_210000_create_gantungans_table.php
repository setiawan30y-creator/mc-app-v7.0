<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gantungans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id');
            $table->ulid('branch_id')->nullable();
            $table->string('gantungan_no', 50);
            $table->date('business_date');
            $table->string('category', 30);
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->decimal('amount', 20, 2)->default(0);
            $table->decimal('settled_amount', 20, 2)->default(0);
            $table->decimal('outstanding_amount', 20, 2)->default(0);
            $table->string('counterparty_name', 150)->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('status', 20)->default('open');
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('settled_by')->nullable();
            $table->dateTime('settled_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'branch_id', 'gantungan_no']);
            $table->index(['tenant_id', 'branch_id', 'business_date']);
            $table->index(['tenant_id', 'branch_id', 'status']);
            $table->index(['tenant_id', 'branch_id', 'category']);
        });

        Schema::create('gantungan_settlements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('gantungan_id');
            $table->decimal('amount', 20, 2);
            $table->dateTime('settled_at');
            $table->string('method', 30)->default('cash');
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('gantungan_id')->references('id')->on('gantungans')->cascadeOnDelete();
            $table->index(['gantungan_id', 'settled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gantungan_settlements');
        Schema::dropIfExists('gantungans');
    }
};
