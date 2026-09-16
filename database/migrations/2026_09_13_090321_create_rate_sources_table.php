<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_sources', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->string('name', 100);
            $table->string('code', 30);
            $table->string('type', 30)->default('reference');

            $table->string('url', 500)->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_sources');
    }
};