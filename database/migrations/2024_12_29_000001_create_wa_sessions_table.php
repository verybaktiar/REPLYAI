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
        Schema::create('wa_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->nullable();
            $table->string('session_id')->unique()->default('default');
            $table->enum('status', ['disconnected', 'waiting_qr', 'connecting', 'connected'])->default('disconnected');
            $table->text('credentials')->nullable(); // For future use
            $table->string('name')->nullable(); // WhatsApp profile name
            $table->timestamp('last_connected_at')->nullable();
            $table->boolean('auto_reply_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_sessions');
    }
};
