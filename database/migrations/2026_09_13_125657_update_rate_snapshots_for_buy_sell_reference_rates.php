<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Sebelumnya:
         * reference_rate
         *
         * Sekarang:
         * reference_buy_rate
         * reference_sell_rate
         */

        Schema::table('rate_snapshots', function (Blueprint $table) {
            $table->decimal('reference_buy_rate', 20, 6)
                ->nullable()
                ->after('effective_at');

            $table->decimal('reference_sell_rate', 20, 6)
                ->nullable()
                ->after('reference_buy_rate');
        });

        /*
         * Migrasikan data lama.
         *
         * Data reference_rate lama kita jadikan sementara
         * sebagai kedua sisi acuan agar histori lama tidak hilang.
         */
        DB::table('rate_snapshots')->update([
            'reference_buy_rate' => DB::raw('reference_rate'),
            'reference_sell_rate' => DB::raw('reference_rate'),
        ]);

        Schema::table('rate_snapshots', function (Blueprint $table) {
            $table->dropColumn('reference_rate');
        });
    }

    public function down(): void
    {
        Schema::table('rate_snapshots', function (Blueprint $table) {
            $table->decimal('reference_rate', 20, 6)
                ->nullable()
                ->after('effective_at');
        });

        /*
         * Saat rollback, gunakan reference_buy_rate
         * sebagai reference_rate lama.
         */
        DB::table('rate_snapshots')->update([
            'reference_rate' => DB::raw('reference_buy_rate'),
        ]);

        Schema::table('rate_snapshots', function (Blueprint $table) {
            $table->dropColumn([
                'reference_buy_rate',
                'reference_sell_rate',
            ]);
        });
    }
};