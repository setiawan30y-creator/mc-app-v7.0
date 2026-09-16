<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('iso_currency_entities', function (Blueprint $table) {
            $table->string('country_code', 2)
                ->nullable()
                ->change();

            $table->unique(
                ['iso_currency_id', 'entity_name'],
                'iso_currency_entities_currency_entity_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iso_currency_entities', function (Blueprint $table) {
            $table->dropUnique(
                'iso_currency_entities_currency_entity_unique'
            );

            $table->string('country_code', 2)
                ->nullable(false)
                ->change();
        });
    }
};