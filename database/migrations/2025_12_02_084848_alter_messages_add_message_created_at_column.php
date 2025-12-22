<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // timestamp asli dari chatwoot (epoch seconds)
            $table->unsignedBigInteger('message_created_at')
                  ->nullable()
                  ->after('content');

            // index biar sorting cepat
            $table->index('message_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['message_created_at']);
            $table->dropColumn('message_created_at');
        });
    }
};
