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
        Schema::create('wa_messages', function (Blueprint $table) {
            $table->id();
            $table->string('wa_message_id')->unique();
            $table->string('remote_jid'); // Full JID (phone@s.whatsapp.net)
            $table->string('phone_number'); // Just the phone number
            $table->string('push_name')->nullable(); // Contact name from WhatsApp
            $table->enum('direction', ['incoming', 'outgoing']);
            $table->text('message');
            $table->string('message_type')->default('text'); // text, image, video, audio, document, etc
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->text('bot_reply')->nullable(); // Bot's reply to this message
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamp('wa_timestamp')->nullable(); // Original WA timestamp
            $table->timestamps();

            $table->index('phone_number');
            $table->index('direction');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
    }
};
