<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_reply_logs', function (Blueprint $table) {

            // sumber jawaban: 'manual' atau 'ai'
            if (!Schema::hasColumn('auto_reply_logs', 'response_source')) {
                $table->string('response_source', 20)
                    ->nullable()
                    ->after('ai_used'); 
            }

            // confidence AI (0-1)
            if (!Schema::hasColumn('auto_reply_logs', 'ai_confidence')) {
                $table->float('ai_confidence')
                    ->nullable()
                    ->after('response_source');
            }

            // sources AI (list artikel KB)
            if (!Schema::hasColumn('auto_reply_logs', 'ai_sources')) {
                $table->json('ai_sources')
                    ->nullable()
                    ->after('ai_confidence');
            }

        });
    }

    public function down(): void
    {
        Schema::table('auto_reply_logs', function (Blueprint $table) {
            if (Schema::hasColumn('auto_reply_logs', 'ai_sources')) {
                $table->dropColumn('ai_sources');
            }
            if (Schema::hasColumn('auto_reply_logs', 'ai_confidence')) {
                $table->dropColumn('ai_confidence');
            }
            if (Schema::hasColumn('auto_reply_logs', 'response_source')) {
                $table->dropColumn('response_source');
            }
        });
    }
};
