<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('web_conversation_id')->constrained()->onDelete('cascade');
            $table->enum('sender_type', ['visitor', 'bot', 'agent']);
            $table->text('content');
            $table->json('metadata')->nullable();      // Data tambahan (attachment, dll)
            $table->timestamps();
            
            $table->index('web_conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_messages');
    }
};
