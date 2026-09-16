<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id')->nullable();
            $table->ulid('branch_id')->nullable();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->ulid('role_id');

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

            $table->unique(
                ['tenant_id', 'branch_id', 'user_id', 'role_id'],
                'user_roles_scope_unique'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'user_id'],
                'user_roles_scope_user_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};