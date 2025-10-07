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
        Schema::create('currency_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3)->index(); // USD
            $table->string('to_currency', 3)->index(); // UZS
            $table->decimal('rate', 12, 4); // Exchange rate
            $table->boolean('is_active')->default(true);
            $table->timestamp('effective_date')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            //            $table->unique(['from_currency', 'to_currency', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_exchange_rates');
    }
};
