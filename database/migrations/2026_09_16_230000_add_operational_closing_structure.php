<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->date('business_date')->nullable()->after('branch_id');
            $table->string('shift', 20)->nullable()->after('business_date');
            $table->string('closing_type', 30)->nullable()->after('shift');
            $table->decimal('opening_cash_amount', 20, 2)->default(0)->after('closing_type');
            $table->decimal('expected_cash_amount', 20, 2)->default(0)->after('opening_cash_amount');
            $table->decimal('physical_cash_amount', 20, 2)->default(0)->after('expected_cash_amount');
            $table->decimal('cash_difference_amount', 20, 2)->default(0)->after('physical_cash_amount');
            $table->decimal('hanging_amount', 20, 2)->default(0)->after('cash_difference_amount');
            $table->decimal('bank_system_amount', 20, 2)->default(0)->after('hanging_amount');
            $table->decimal('bank_physical_amount', 20, 2)->default(0)->after('bank_system_amount');
            $table->decimal('bank_difference_amount', 20, 2)->default(0)->after('bank_physical_amount');

            $table->index(
                ['tenant_id', 'branch_id', 'business_date', 'shift'],
                'cash_closings_scope_business_shift_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->dropIndex('cash_closings_scope_business_shift_index');
            $table->dropColumn([
                'business_date',
                'shift',
                'closing_type',
                'opening_cash_amount',
                'expected_cash_amount',
                'physical_cash_amount',
                'cash_difference_amount',
                'hanging_amount',
                'bank_system_amount',
                'bank_physical_amount',
                'bank_difference_amount',
            ]);
        });
    }
};
