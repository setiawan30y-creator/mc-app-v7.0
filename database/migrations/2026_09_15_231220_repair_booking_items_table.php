<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->ulid('booking_id')->nullable();

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('currency_variant_id')->nullable();
            $table->unsignedBigInteger('currency_denomination_id')->nullable();

            $table->string('direction', 20)->nullable();

            $table->decimal('quantity', 20, 6)->default(0);

            $table->decimal('requested_rate', 20, 6)->nullable();
            $table->decimal('estimated_subtotal', 20, 6)->nullable();

            $table->text('notes')->nullable();

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
                ['currency_id', 'currency_variant_id', 'currency_denomination_id'],
                'booking_items_currency_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropForeign(['currency_denomination_id']);
            $table->dropForeign(['currency_variant_id']);
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['booking_id']);

            $table->dropIndex('booking_items_booking_direction_index');
            $table->dropIndex('booking_items_currency_index');

            $table->dropColumn([
                'booking_id',
                'currency_id',
                'currency_variant_id',
                'currency_denomination_id',
                'direction',
                'quantity',
                'requested_rate',
                'estimated_subtotal',
                'notes',
            ]);
        });
    }
};