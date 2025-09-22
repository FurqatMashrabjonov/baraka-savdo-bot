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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->string('phone')->nullable();
            $table->string('full_name')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // make phone and chat id unique if deleted_at is null
            $table->unique(['phone', 'deleted_at']);
            $table->unique(['chat_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
