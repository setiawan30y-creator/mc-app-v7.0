<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->ulid('tenant_id')->nullable();
            $table->ulid('branch_id')->nullable();
            $table->string('booking_no', 50)->nullable();
            $table->ulid('customer_id')->nullable();

            $table->dateTime('booking_date')->nullable();
            $table->dateTime('expiry_at')->nullable();

            $table->string('status', 30)->default('draft');

            $table->decimal('deposit_amount', 20, 6)->default(0);
            $table->unsignedBigInteger('deposit_currency_id')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->dateTime('fulfilled_at')->nullable();
            $table->ulid('fulfilled_transaction_id')->nullable();

            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->cascadeOnDelete();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->restrictOnDelete();

            $table->foreign('deposit_currency_id')
                ->references('id')
                ->on('currencies')
                ->restrictOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('fulfilled_transaction_id')
                ->references('id')
                ->on('mc_transactions')
                ->nullOnDelete();

            $table->unique(
                ['tenant_id', 'booking_no'],
                'bookings_tenant_booking_no_unique'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'booking_date'],
                'bookings_scope_date_index'
            );

            $table->index(
                ['tenant_id', 'customer_id'],
                'bookings_customer_index'
            );

            $table->index(
                ['tenant_id', 'status'],
                'bookings_status_index'
            );

            $table->index(
                ['tenant_id', 'expiry_at'],
                'bookings_expiry_index'
            );

            $table->index(
                ['fulfilled_transaction_id'],
                'bookings_fulfilled_transaction_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['fulfilled_transaction_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['deposit_currency_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['tenant_id']);

            $table->dropUnique('bookings_tenant_booking_no_unique');

            $table->dropIndex('bookings_scope_date_index');
            $table->dropIndex('bookings_customer_index');
            $table->dropIndex('bookings_status_index');
            $table->dropIndex('bookings_expiry_index');
            $table->dropIndex('bookings_fulfilled_transaction_index');

            $table->dropColumn([
                'tenant_id',
                'branch_id',
                'booking_no',
                'customer_id',
                'booking_date',
                'expiry_at',
                'status',
                'deposit_amount',
                'deposit_currency_id',
                'notes',
                'created_by',
                'fulfilled_at',
                'fulfilled_transaction_id',
                'cancelled_at',
                'cancellation_reason',
            ]);
        });
    }
};