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
        Schema::create('parcels', function (Blueprint $table) {
            $table->id();
            $table->string('track_number')->unique();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('weight', 8, 3)->nullable();
            $table->string('status')->default('created');
            $table->boolean('is_banned')->default(false);
            $table->timestamp('china_uploaded_at')->nullable();
            $table->timestamp('uzb_uploaded_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['track_number']);
            $table->index(['client_id']);
            $table->index(['china_uploaded_at']);
            $table->index(['uzb_uploaded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcels');
    }
};
