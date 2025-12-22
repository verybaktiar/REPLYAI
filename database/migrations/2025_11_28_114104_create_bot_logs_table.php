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
        Schema::create('bot_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                  ->constrained('conversations')
                  ->cascadeOnDelete();

            $table->foreignId('message_id')
                  ->nullable()
                  ->constrained('messages')
                  ->nullOnDelete();

            $table->foreignId('bot_rule_id')
                  ->nullable()
                  ->constrained('bot_rules')
                  ->nullOnDelete();

            $table->enum('status', ['sent', 'skipped', 'failed'])->default('sent')->index();
            $table->text('error_message')->nullable();

            $table->timestamp('sent_at')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_logs');
    }
};
