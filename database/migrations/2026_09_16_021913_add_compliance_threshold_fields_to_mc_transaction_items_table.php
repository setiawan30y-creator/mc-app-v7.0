<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mc_transaction_items', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Compliance Threshold Snapshot
            |--------------------------------------------------------------------------
            |
            | Menyimpan rule yang benar-benar digunakan oleh item transaksi.
            | Ini penting untuk audit apabila aturan threshold berubah di masa
            | depan.
            |
            */

            $table->ulid('compliance_threshold_rule_id')
                ->nullable()
                ->after('threshold_rate_snapshot_id');

            /*
            |--------------------------------------------------------------------------
            | Equivalent Amount
            |--------------------------------------------------------------------------
            |
            | Nilai transaksi dalam basis threshold yang digunakan.
            |
            | Contoh:
            | basis USD -> 100.00
            | basis IDR -> 20,000,000.00
            |
            */

            $table->decimal('threshold_equivalent_amount', 20, 2)
                ->nullable()
                ->after('compliance_threshold_rule_id');

            /*
            |--------------------------------------------------------------------------
            | Basis Currency
            |--------------------------------------------------------------------------
            |
            | Menyimpan currency yang digunakan sebagai basis threshold.
            |
            | USD -> currency_id USD
            | IDR -> currency_id IDR
            |
            */

            $table->unsignedBigInteger('threshold_currency_id')
                ->nullable()
                ->after('threshold_equivalent_amount');

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign('compliance_threshold_rule_id')
                ->references('id')
                ->on('compliance_threshold_rules')
                ->restrictOnDelete();

            $table->foreign('threshold_currency_id')
                ->references('id')
                ->on('currencies')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                'compliance_threshold_rule_id',
                'mc_transaction_items_threshold_rule_idx'
            );

            $table->index(
                'threshold_currency_id',
                'mc_transaction_items_threshold_currency_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('mc_transaction_items', function (Blueprint $table) {

            $table->dropForeign([
                'compliance_threshold_rule_id',
            ]);

            $table->dropForeign([
                'threshold_currency_id',
            ]);

            $table->dropIndex(
                'mc_transaction_items_threshold_rule_idx'
            );

            $table->dropIndex(
                'mc_transaction_items_threshold_currency_idx'
            );

            $table->dropColumn([
                'compliance_threshold_rule_id',
                'threshold_equivalent_amount',
                'threshold_currency_id',
            ]);
        });
    }
};