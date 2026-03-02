<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['welcome', 'away', 'follow_up', 'keyword']);
            $table->string('name');
            $table->text('message');
            $table->boolean('is_active')->default(true);
            
            // Untuk away message
            $table->time('away_start_time')->nullable();
            $table->time('away_end_time')->nullable();
            $table->json('away_days')->nullable(); // ["monday", "tuesday", ...]
            
            // Untuk keyword trigger
            $table->json('keywords')->nullable(); // ["harga", "promo", ...]
            $table->enum('match_type', ['exact', 'contains', 'starts_with'])->default('contains');
            
            // Untuk follow-up
            $table->integer('delay_hours')->nullable();
            
            // Statistik
            $table->integer('trigger_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_automations');
    }
};
