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
        Schema::create('ai_training_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_profile_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('cascade')
                ->comment('Referensi ke conversation asal');
            $table->text('question')->comment('Pertanyaan user yang tidak terjawab AI');
            $table->text('cs_answer')->comment('Jawaban dari CS manusia');
            $table->string('status')->default('pending')->comment('pending, approved, rejected, added_to_kb');
            $table->foreignId('kb_article_id')->nullable()->constrained()->onDelete('set null')
                ->comment('KB article yang dibuat dari suggestion ini');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['user_id', 'status']);
            $table->index(['business_profile_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_training_suggestions');
    }
};
