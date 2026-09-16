<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Semua tabel yang akan direbuild wajib kosong.
         * Migration sengaja gagal jika ternyata sudah ada data.
         */
        if (
            Schema::hasTable('bookings') &&
            \DB::table('bookings')->count() > 0
        ) {
            throw new \RuntimeException(
                'Rebuild dihentikan: tabel bookings masih memiliki data.'
            );
        }

        if (
            Schema::hasTable('booking_items') &&
            \DB::table('booking_items')->count() > 0
        ) {
            throw new \RuntimeException(
                'Rebuild dihentikan: tabel booking_items masih memiliki data.'
            );
        }

        if (
            Schema::hasTable('cash_closings') &&
            \DB::table('cash_closings')->count() > 0
        ) {
            throw new \RuntimeException(
                'Rebuild dihentikan: tabel cash_closings masih memiliki data.'
            );
        }

        if (
            Schema::hasTable('cash_closing_details') &&
            \DB::table('cash_closing_details')->count() > 0
        ) {
            throw new \RuntimeException(
                'Rebuild dihentikan: tabel cash_closing_details masih memiliki data.'
            );
        }

        /*
         * Urutan drop penting karena ada foreign key:
         *
         * cash_closing_details -> cash_closings
         * booking_items        -> bookings
         */
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('cash_closing_details');
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('cash_closings');
        Schema::dropIfExists('bookings');

        Schema::enableForeignKeyConstraints();

        /*
         * ============================================================
         * BOOKINGS
         * ============================================================
         */
        Schema::create('bookings', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('branch_id');

            $table->string('booking_no', 50);

            $table->ulid('customer_id');

            $table->dateTime('booking_date');
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

            $table->timestamps();

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
                ['tenant_id', 'branch_id', 'status'],
                'bookings_scope_status_index'
            );

            $table->index(
                ['customer_id', 'booking_date'],
                'bookings_customer_date_index'
            );

            $table->index(
                ['expiry_at', 'status'],
                'bookings_expiry_status_index'
            );
        });

        /*
         * ============================================================
         * BOOKING ITEMS
         * ============================================================
         */
        Schema::create('booking_items', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('booking_id');

            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('currency_variant_id');
            $table->unsignedBigInteger('currency_denomination_id')->nullable();

            $table->string('direction', 20);

            $table->decimal('quantity', 20, 6)->default(0);

            $table->decimal('requested_rate', 20, 6)->nullable();
            $table->decimal('estimated_subtotal', 20, 6)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings')
                ->cascadeOnDelete();

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->restrictOnDelete();

            $table->foreign('currency_variant_id')
                ->references('id')
                ->on('currency_variants')
                ->restrictOnDelete();

            $table->foreign('currency_denomination_id')
                ->references('id')
                ->on('currency_denominations')
                ->restrictOnDelete();

            $table->index(
                ['booking_id', 'direction'],
                'booking_items_booking_direction_index'
            );

            $table->index(
                [
                    'currency_id',
                    'currency_variant_id',
                    'currency_denomination_id',
                ],
                'booking_items_currency_index'
            );
        });

        /*
         * ============================================================
         * CASH CLOSINGS
         * ============================================================
         */
        Schema::create('cash_closings', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('branch_id');

            $table->string('closing_no', 50);

            $table->dateTime('closing_date');

            $table->string('status', 30)->default('draft');

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->dateTime('approved_at')->nullable();

            $table->dateTime('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->cascadeOnDelete();

            $table->foreign('prepared_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(
                ['tenant_id', 'branch_id', 'closing_no'],
                'cash_closings_scope_closing_no_unique'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'closing_date'],
                'cash_closings_scope_date_index'
            );

            $table->index(
                ['tenant_id', 'branch_id', 'status'],
                'cash_closings_scope_status_index'
            );
        });

        /*
         * ============================================================
         * CASH CLOSING DETAILS
         * ============================================================
         */
        Schema::create('cash_closing_details', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('closing_id');

            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('currency_variant_id');
            $table->unsignedBigInteger('currency_denomination_id');

            $table->decimal('system_quantity', 20, 6)->default(0);
            $table->decimal('physical_quantity', 20, 6)->default(0);
            $table->decimal('difference_quantity', 20, 6)->default(0);

            $table->decimal('system_amount', 20, 6)->default(0);
            $table->decimal('physical_amount', 20, 6)->default(0);
            $table->decimal('difference_amount', 20, 6)->default(0);

            $table->string('adjustment_status', 30)->default('none');

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->dateTime('approved_at')->nullable();

            $table->ulid('adjustment_movement_id')->nullable();

            $table->timestamps();

            $table->foreign('closing_id')
                ->references('id')
                ->on('cash_closings')
                ->cascadeOnDelete();

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->restrictOnDelete();

            $table->foreign('currency_variant_id')
                ->references('id')
                ->on('currency_variants')
                ->restrictOnDelete();

            $table->foreign('currency_denomination_id')
                ->references('id')
                ->on('currency_denominations')
                ->restrictOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('adjustment_movement_id')
                ->references('id')
                ->on('cash_inventory_movements')
                ->nullOnDelete();

            $table->unique(
                ['closing_id', 'currency_denomination_id'],
                'cash_closing_details_closing_denomination_unique'
            );

            $table->index(
                ['currency_id', 'currency_variant_id', 'currency_denomination_id'],
                'cash_closing_details_currency_index'
            );

            $table->index(
                ['closing_id', 'adjustment_status'],
                'cash_closing_details_adjustment_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('cash_closing_details');
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('cash_closings');
        Schema::dropIfExists('bookings');

        Schema::enableForeignKeyConstraints();
    }
};