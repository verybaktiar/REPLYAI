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
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_profile_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable(); // Sender ID
            $table->text('user_message');
            $table->text('answer')->nullable();
            $table->float('confidence')->default(0);
            $table->string('source')->nullable(); // 'kb' or 'ai' or 'fallback'
            $table->json('context_used')->nullable(); // IDs of KB articles used
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->timestamps();

            // Index for faster querying
            $table->index(['business_profile_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
