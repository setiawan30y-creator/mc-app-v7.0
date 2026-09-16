<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_risk_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            $table->string('type', 30);
            $table->string('name', 150);
            $table->string('code', 30)->nullable();

            $table->string('risk_level', 20)->default('low');
            $table->unsignedInteger('risk_score')->default(0);

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'risk_level']);
            $table->unique(['tenant_id', 'type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_risk_masters');
    }
};
