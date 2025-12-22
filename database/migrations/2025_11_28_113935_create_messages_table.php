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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // ID message dari Chatwoot
            $table->unsignedBigInteger('chatwoot_id')->unique();

            // relasi ke conversation lokal
            $table->foreignId('conversation_id')
                  ->constrained('conversations')
                  ->cascadeOnDelete();

            // sender
            $table->enum('sender_type', ['contact', 'user', 'agent', 'bot'])->default('contact')->index();

            $table->text('content')->nullable();

            // timestamp asli dari chatwoot
            $table->unsignedBigInteger('created_at_chatwoot')->nullable()->index();

            // optional metadata
            $table->json('raw_payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};