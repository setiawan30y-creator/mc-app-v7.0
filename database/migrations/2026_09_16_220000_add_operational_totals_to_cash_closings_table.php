<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->decimal('expected_amount', 20, 2)->default(0)->after('status');
            $table->decimal('physical_amount', 20, 2)->default(0)->after('expected_amount');
            $table->decimal('difference_amount', 20, 2)->default(0)->after('physical_amount');
            $table->unsignedBigInteger('closed_by')->nullable()->after('approved_by');
            $table->dateTime('closed_at')->nullable()->after('closed_by');

            $table->index(
                ['tenant_id', 'branch_id', 'closing_date', 'status'],
                'cash_closings_operational_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->dropIndex('cash_closings_operational_index');
            $table->dropColumn([
                'expected_amount',
                'physical_amount',
                'difference_amount',
                'closed_by',
                'closed_at',
            ]);
        });
    }
};