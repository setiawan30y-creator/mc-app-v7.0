<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rate_snapshots', function (Blueprint $table) {
            $table->foreignId('currency_variant_id')
                ->nullable()
                ->after('currency_id')
                ->constrained('currency_variants')
                ->restrictOnDelete();

            $table->foreignId('currency_denomination_id')
                ->nullable()
                ->after('currency_variant_id')
                ->constrained('currency_denominations')
                ->restrictOnDelete();

            $table->index([
                'tenant_id',
                'currency_id',
                'currency_variant_id',
                'currency_denomination_id',
                'effective_at',
            ], 'rate_snapshots_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('rate_snapshots', function (Blueprint $table) {
            $table->dropIndex('rate_snapshots_lookup_index');

            $table->dropForeign(['currency_denomination_id']);
            $table->dropForeign(['currency_variant_id']);

            $table->dropColumn([
                'currency_denomination_id',
                'currency_variant_id',
            ]);
        });
    }
};