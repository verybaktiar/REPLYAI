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
        // Add session timeout settings to wa_sessions
        Schema::table('wa_sessions', function (Blueprint $table) {
            $table->integer('session_idle_timeout_minutes')->default(30)->after('idle_warning_minutes');
            $table->integer('session_followup_timeout_minutes')->default(15)->after('session_idle_timeout_minutes');
        });

        // Add session tracking fields to wa_conversations
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->timestamp('last_user_reply_at')->nullable()->after('last_cs_reply_at');
            $table->timestamp('followup_sent_at')->nullable()->after('last_user_reply_at');
            $table->string('session_status')->default('active')->after('status'); // active, followup_sent, closed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_sessions', function (Blueprint $table) {
            $table->dropColumn(['session_idle_timeout_minutes', 'session_followup_timeout_minutes']);
        });

        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->dropColumn(['last_user_reply_at', 'followup_sent_at', 'session_status']);
        });
    }
};
