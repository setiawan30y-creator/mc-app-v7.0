<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();

            // Relasi 1 : 1 dengan tenant
            $table->ulid('tenant_id')->unique();

            // Profil perusahaan
            $table->string('company_name')->nullable();
            $table->string('company_short_name', 100)->nullable();

            // Legalitas
            $table->string('idpjk', 100)->nullable();
            $table->string('npwp', 50)->nullable();
            $table->string('license_number', 100)->nullable();

            // Alamat
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Indonesia');

            // Kontak
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website', 255)->nullable();

            // Logo
            $table->string('logo_path')->nullable();

            // Pengaturan sistem
            $table->string('timezone', 100)->default('Asia/Jakarta');
            $table->string('locale', 10)->default('id');
            $table->string('default_currency', 10)->default('IDR');

            // Pengaturan struk
            $table->text('receipt_header')->nullable();
            $table->text('receipt_footer')->nullable();

            $table->timestamps();

            // Foreign key ke tenants.id yang bertipe ULID
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};