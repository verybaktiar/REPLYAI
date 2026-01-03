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
        // Add takeover settings to wa_sessions
        Schema::table('wa_sessions', function (Blueprint $table) {
            $table->integer('takeover_timeout_minutes')->default(60)->after('auto_reply_enabled');
            $table->integer('idle_warning_minutes')->default(30)->after('takeover_timeout_minutes');
        });

        // Add takeover fields to conversations (Instagram)
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('assigned_cs')->nullable()->after('status');
            $table->timestamp('takeover_at')->nullable()->after('assigned_cs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_sessions', function (Blueprint $table) {
            $table->dropColumn(['takeover_timeout_minutes', 'idle_warning_minutes']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['assigned_cs', 'takeover_at']);
        });
    }
};
