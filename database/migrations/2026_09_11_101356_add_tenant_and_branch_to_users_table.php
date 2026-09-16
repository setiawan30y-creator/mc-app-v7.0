<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->ulid('tenant_id')
                ->nullable()
                ->after('id');

            $table->ulid('branch_id')
                ->nullable()
                ->after('tenant_id');

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->nullOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();

            $table->index(['tenant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['tenant_id']);

            $table->dropIndex(['tenant_id', 'branch_id']);

            $table->dropColumn([
                'tenant_id',
                'branch_id',
            ]);
        });
    }
};