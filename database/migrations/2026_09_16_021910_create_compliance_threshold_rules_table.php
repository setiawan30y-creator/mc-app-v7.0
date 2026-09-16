<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_threshold_rules', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Multi-tenant isolation
            $table->ulid('tenant_id');
            
            // Rule identity
            $table->string('name', 150);
            $table->string('code', 100);

            // Compliance calculation basis:
            // usd = USD equivalent
            // idr = IDR equivalent
            $table->string('basis', 20);

            // Threshold limit in the selected basis currency
            $table->decimal('limit_amount', 20, 2);

            // Period used for aggregation
            // monthly / daily / yearly
            $table->string('period', 20)->default('monthly');

            // Rule validity
            $table->dateTime('effective_from');
            $table->dateTime('effective_until')->nullable();

            // Whether the rule can currently be used
            $table->boolean('is_active')->default(true);

            // Optional regulatory reference
            $table->string('regulation_reference', 255)->nullable();

            // Internal notes
            $table->text('notes')->nullable();

            // Audit
            // users.id is BIGINT UNSIGNED in this application,
            // therefore these are intentionally unsignedBigInteger.
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['tenant_id', 'is_active'],
                'compliance_threshold_rules_tenant_active_idx'
            );

            $table->index(
                ['tenant_id', 'effective_from', 'effective_until'],
                'compliance_threshold_rules_validity_idx'
            );

            $table->index(
                ['tenant_id', 'basis', 'period'],
                'compliance_threshold_rules_basis_period_idx'
            );

            $table->unique(
                ['tenant_id', 'code'],
                'compliance_threshold_rules_tenant_code_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_threshold_rules');
    }
};