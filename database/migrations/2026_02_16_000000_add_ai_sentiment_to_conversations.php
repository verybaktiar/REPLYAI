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
        // Add to Instagram conversations
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('ai_sentiment', 20)->nullable()->after('status')->index();
            $table->decimal('ai_sentiment_score', 3, 2)->nullable()->after('ai_sentiment');
            $table->string('ai_intent', 30)->nullable()->after('ai_sentiment_score')->index();
            $table->decimal('ai_intent_confidence', 3, 2)->nullable()->after('ai_intent');
            $table->timestamp('ai_analyzed_at')->nullable()->after('ai_intent_confidence');
        });

        // Add to WhatsApp conversations
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->string('ai_sentiment', 20)->nullable()->after('status')->index();
            $table->decimal('ai_sentiment_score', 3, 2)->nullable()->after('ai_sentiment');
            $table->string('ai_intent', 30)->nullable()->after('ai_sentiment_score')->index();
            $table->decimal('ai_intent_confidence', 3, 2)->nullable()->after('ai_intent');
            $table->timestamp('ai_analyzed_at')->nullable()->after('ai_intent_confidence');
        });

        // Add to Web conversations
        Schema::table('web_conversations', function (Blueprint $table) {
            $table->string('ai_sentiment', 20)->nullable()->after('status')->index();
            $table->decimal('ai_sentiment_score', 3, 2)->nullable()->after('ai_sentiment');
            $table->string('ai_intent', 30)->nullable()->after('ai_sentiment_score')->index();
            $table->decimal('ai_intent_confidence', 3, 2)->nullable()->after('ai_intent');
            $table->timestamp('ai_analyzed_at')->nullable()->after('ai_intent_confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['ai_sentiment', 'ai_sentiment_score', 'ai_intent', 'ai_intent_confidence', 'ai_analyzed_at']);
        });

        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->dropColumn(['ai_sentiment', 'ai_sentiment_score', 'ai_intent', 'ai_intent_confidence', 'ai_analyzed_at']);
        });

        Schema::table('web_conversations', function (Blueprint $table) {
            $table->dropColumn(['ai_sentiment', 'ai_sentiment_score', 'ai_intent', 'ai_intent_confidence', 'ai_analyzed_at']);
        });
    }
};
