<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add denomination type:
     * - banknote
     * - coin
     *
     * Existing denominations are treated as banknotes.
     */
    public function up(): void
    {
        Schema::table('currency_denominations', function (Blueprint $table) {
            $table->string('type', 20)
                ->default('banknote')
                ->after('value');
        });

        Schema::table('currency_denominations', function (Blueprint $table) {
            $table->dropUnique(
                'currency_denominations_currency_variant_id_value_unique'
            );

            $table->unique(
                ['currency_variant_id', 'value', 'type'],
                'currency_denominations_variant_value_type_unique'
            );
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        /*
         * The old unique constraint only allows one denomination
         * per value inside a variant.
         *
         * Therefore rollback is only safe when there are no
         * duplicate (currency_variant_id, value) combinations.
         */
        $duplicates = \DB::table('currency_denominations')
            ->select('currency_variant_id', 'value')
            ->groupBy('currency_variant_id', 'value')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicates) {
            throw new \RuntimeException(
                'Cannot rollback currency denomination type migration: '
                . 'duplicate denomination values exist within the same currency variant.'
            );
        }

        Schema::table('currency_denominations', function (Blueprint $table) {
            $table->dropUnique(
                'currency_denominations_variant_value_type_unique'
            );

            $table->unique(
                ['currency_variant_id', 'value'],
                'currency_denominations_currency_variant_id_value_unique'
            );

            $table->dropColumn('type');
        });
    }
};