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
        Schema::create('csat_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Owner of the conversation
            $table->string('platform'); // 'instagram' or 'whatsapp'
            $table->unsignedBigInteger('conversation_id')->nullable(); // IG conversation
            $table->unsignedBigInteger('wa_conversation_id')->nullable(); // WA conversation
            $table->string('contact_identifier'); // Phone number or IG PSID
            $table->string('contact_name')->nullable();
            $table->tinyInteger('rating')->unsigned(); // 1-5 stars
            $table->text('feedback')->nullable(); // Optional text feedback
            $table->string('handled_by')->default('bot'); // 'bot' or 'agent'
            $table->timestamp('requested_at')->nullable(); // When CSAT was sent
            $table->timestamp('responded_at')->nullable(); // When customer responded
            $table->timestamps();

            $table->index(['user_id', 'platform']);
            $table->index(['user_id', 'rating']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csat_ratings');
    }
};
