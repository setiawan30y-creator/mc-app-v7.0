<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->ulid('tenant_id')->nullable();
            $table->ulid('branch_id')->nullable();

            $table->string('closing_no', 50)->nullable();
            $table->dateTime('closing_date')->nullable();

            $table->string('status', 30)->default('draft');

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->cascadeOnDelete();

            $table->foreign('prepared_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(
                ['tenant_id', 'branch_id', 'closing_no'],
                'cash_closings_scope_closing_no_unique'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'closing_date'],
                'cash_closings_scope_date_index'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'status'],
                'cash_closings_scope_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['prepared_by']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['tenant_id']);

            $table->dropUnique(
                'cash_closings_scope_closing_no_unique'
            );

            $table->dropIndex(
                'cash_closings_scope_date_index'
            );

            $table->dropIndex(
                'cash_closings_scope_status_index'
            );

            $table->dropColumn([
                'tenant_id',
                'branch_id',
                'closing_no',
                'closing_date',
                'status',
                'notes',
                'prepared_by',
                'approved_by',
                'approved_at',
                'rejected_at',
                'rejection_reason',
            ]);
        });
    }
};