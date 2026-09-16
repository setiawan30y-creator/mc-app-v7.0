<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id')->nullable();
            $table->ulid('branch_id')->nullable();

            $table->ulid('role_id');
            $table->ulid('permission_id');

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->cascadeOnDelete();

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->cascadeOnDelete();

            $table->unique(
                ['tenant_id', 'branch_id', 'role_id', 'permission_id'],
                'role_permissions_scope_unique'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'role_id'],
                'role_permissions_scope_role_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};