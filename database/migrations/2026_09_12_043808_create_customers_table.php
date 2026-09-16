<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Tenant & cabang pemilik customer.
            $table->ulid('tenant_id');
            $table->ulid('branch_id');

            // Nomor customer internal.
            $table->string('customer_number', 50);

            // Identitas dasar.
            $table->string('full_name');
            $table->string('display_name')->nullable();

            // Jenis customer.
            $table->string('customer_type', 30)->default('individual');

            // Identitas resmi.
            $table->string('identity_type', 30)->nullable();
            $table->string('identity_number', 100)->nullable();

            // Informasi kontak.
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            // Data dasar customer.
            $table->date('birth_date')->nullable();
            $table->string('nationality', 3)->nullable();

            // Alamat.
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('country', 100)->nullable();

            // Status customer.
            $table->string('status', 20)->default('active');

            // Status KYC dasar.
            $table->string('kyc_status', 30)->default('pending');

            // Metadata audit.
            $table->ulid('created_by')->nullable();
            $table->ulid('updated_by')->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->cascadeOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Nomor customer unik dalam tenant.
            $table->unique(
                ['tenant_id', 'customer_number'],
                'customers_tenant_number_unique'
            );

            // Index untuk pencarian operasional.
            $table->index(
                ['tenant_id', 'branch_id', 'status'],
                'customers_scope_status_index'
            );

            $table->index(
                ['tenant_id', 'identity_type', 'identity_number'],
                'customers_identity_index'
            );

            $table->index(
                ['tenant_id', 'phone'],
                'customers_phone_index'
            );

            $table->index(
                ['tenant_id', 'full_name'],
                'customers_name_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};