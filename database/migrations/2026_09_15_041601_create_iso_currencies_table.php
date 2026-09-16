<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_currencies', function (Blueprint $table) {
            $table->id();

            /*
             * ISO 4217 alphabetic currency code.
             * Contoh: USD, EUR, BHD, KWD.
             */
            $table->string('code', 3)->unique();

            /*
             * ISO 4217 numeric currency code.
             */
            $table->string('numeric_code', 3)->nullable();

            /*
             * Nama resmi currency.
             */
            $table->string('name', 150);

            /*
             * ISO 4217 minor unit.
             * Contoh:
             * USD = 2
             * JPY = 0
             * BHD = 3
             */
            $table->unsignedTinyInteger('minor_unit')->nullable();

            /*
             * Apakah entry masih termasuk
             * current ISO catalog.
             */
            $table->boolean('is_active')->default(true);

            /*
             * Waktu terakhir data ISO ini disinkronkan.
             */
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'code']);
            $table->index('numeric_code');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_currencies');
    }
};
