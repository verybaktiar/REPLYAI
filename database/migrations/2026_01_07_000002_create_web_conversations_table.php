<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->constrained('web_widgets')->onDelete('cascade');
            $table->string('visitor_id');              // UUID untuk visitor anonymous
            $table->string('visitor_name')->nullable();
            $table->string('visitor_email')->nullable();
            $table->string('visitor_ip')->nullable();
            $table->string('visitor_user_agent')->nullable();
            $table->string('page_url')->nullable();    // Halaman saat mulai chat
            $table->text('last_message')->nullable();
            $table->enum('status', ['bot', 'cs', 'escalated', 'closed'])->default('bot');
            $table->timestamp('last_activity_at')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            
            $table->index(['widget_id', 'visitor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_conversations');
    }
};
