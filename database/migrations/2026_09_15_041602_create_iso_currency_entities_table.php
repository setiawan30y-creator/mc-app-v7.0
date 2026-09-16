<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_currency_entities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('iso_currency_id')
                ->constrained('iso_currencies')
                ->cascadeOnDelete();

            /*
             * ISO 3166-1 alpha-2 country/entity code.
             * Contoh: BH, KW, OM, FR.
             */
            $table->string('country_code', 2);

            /*
             * Negara / entitas yang menggunakan currency.
             */
            $table->string('entity_name', 150);

            $table->timestamps();

            $table->unique(
                ['iso_currency_id', 'country_code'],
                'iso_currency_entity_unique'
            );

            $table->index('country_code');
            $table->index('entity_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_currency_entities');
    }
};
