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
        // Drop existing tables to start fresh
        Schema::dropIfExists('clients');
        Schema::dropIfExists('telegram_accounts');

        // Create clients table with phone as unique identifier
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('full_name')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create telegram_accounts table with client_id foreign key
        Schema::create('telegram_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('lang')->nullable();
            $table->dateTime('channel_joined_at')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_accounts');
        Schema::dropIfExists('clients');
    }
};
