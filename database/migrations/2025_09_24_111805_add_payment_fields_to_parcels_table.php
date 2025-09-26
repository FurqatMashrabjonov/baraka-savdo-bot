<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->default('pending')->after('status');
            $table->timestamp('payment_date')->nullable()->after('payment_status');
            $table->enum('payment_type', ['online', 'offline'])->default('offline')->after('payment_date');
            $table->decimal('payment_amount_usd', 10, 2)->nullable()->after('payment_type');
            $table->decimal('payment_amount_uzs', 15, 2)->nullable()->after('payment_amount_usd');
            $table->decimal('exchange_rate', 10, 4)->nullable()->after('payment_amount_uzs');
            $table->text('payment_notes')->nullable()->after('exchange_rate');
            $table->unsignedBigInteger('processed_by')->nullable()->after('payment_notes');

            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn([
                'payment_status',
                'payment_date',
                'payment_type',
                'payment_amount_usd',
                'payment_amount_uzs',
                'exchange_rate',
                'payment_notes',
                'processed_by',
            ]);
        });
    }
};
