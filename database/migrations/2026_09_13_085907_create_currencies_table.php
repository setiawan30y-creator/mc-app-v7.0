<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            /*
             * ISO 4217
             */
            $table->string('code', 3)->unique();
            $table->string('name', 100);
            $table->string('name_local', 100)->nullable();

            /*
             * ISO 4217 numeric code jika tersedia.
             */
            $table->string('numeric_code', 3)->nullable();

            /*
             * Flag negara / region.
             * Contoh: 🇺🇸, 🇸🇬, 🇲🇾, 🇪🇺
             */
            $table->string('flag', 10)->nullable();

            /*
             * Jumlah digit desimal yang digunakan
             * untuk nominal mata uang.
             */
            $table->unsignedTinyInteger('decimal_digits')->default(2);

            /*
             * Apakah mata uang masih digunakan.
             */
            $table->boolean('is_active')->default(true);

            /*
             * Urutan tampilan di UI.
             */
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('numeric_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};