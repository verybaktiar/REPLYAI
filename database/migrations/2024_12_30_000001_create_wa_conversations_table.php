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
        Schema::create('wa_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->string('display_name')->nullable();
            $table->enum('status', ['bot_active', 'agent_handling', 'idle'])->default('bot_active');
            $table->string('assigned_cs')->nullable();
            $table->timestamp('takeover_at')->nullable();
            $table->timestamp('last_cs_reply_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('last_cs_reply_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_conversations');
    }
};
