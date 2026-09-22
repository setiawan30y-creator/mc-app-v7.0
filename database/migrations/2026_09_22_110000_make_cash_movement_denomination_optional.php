<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Transaction-level IDR cash payments are aggregate ledger movements;
        // denomination is not known from the payment form and therefore must
        // be nullable. Forex stock remains tracked per denomination separately.
        DB::statement('ALTER TABLE cash_movements MODIFY currency_variant_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE cash_movements MODIFY currency_denomination_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE cash_movements MODIFY currency_variant_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE cash_movements MODIFY currency_denomination_id BIGINT UNSIGNED NOT NULL');
    }
};
