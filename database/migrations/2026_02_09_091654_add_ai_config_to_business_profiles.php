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
        Schema::table('business_profiles', function (Blueprint $table) {
            // AI Configuration
            $table->float('kb_match_threshold')->default(0.35)->after('kb_fallback_message')
                ->comment('Threshold minimum untuk match KB (0.0 - 1.0)');
            $table->integer('ai_rate_limit_per_hour')->default(100)->after('kb_match_threshold')
                ->comment('Limit request AI per jam per user');
            $table->json('custom_synonyms')->nullable()->after('ai_rate_limit_per_hour')
                ->comment('Custom synonym map untuk bisnis spesifik');
            $table->boolean('enable_smart_fallback')->default(true)->after('custom_synonyms')
                ->comment('Aktifkan smart fallback dengan suggestions');
            $table->integer('conversation_memory_limit')->default(10)->after('enable_smart_fallback')
                ->comment('Jumlah message history yang disimpan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'kb_match_threshold',
                'ai_rate_limit_per_hour',
                'custom_synonyms',
                'enable_smart_fallback',
                'conversation_memory_limit',
            ]);
        });
    }
};
