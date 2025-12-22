<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('auto_reply_logs', function (Blueprint $table) {
            $table->boolean('ai_used')->default(false)->after('status');
            $table->float('ai_confidence')->nullable()->after('ai_used');
            $table->json('ai_sources')->nullable()->after('ai_confidence');
        });
    }

    public function down(): void
    {
        Schema::table('auto_reply_logs', function (Blueprint $table) {
            $table->dropColumn(['ai_used', 'ai_confidence', 'ai_sources']);
        });
    }
};
