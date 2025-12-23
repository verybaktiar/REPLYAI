<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Tambah kolom untuk Meta API
            if (!Schema::hasColumn('messages', 'instagram_message_id')) {
                $table->string('instagram_message_id')->nullable()->after('chatwoot_id');
            }
            
            if (!Schema::hasColumn('messages', 'source')) {
                $table->enum('source', ['chatwoot', 'meta_direct'])->default('chatwoot')->after('instagram_message_id');
            }
            
            // Rename atau tambah kolom timestamp yang lebih fleksibel
            if (!Schema::hasColumn('messages', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('message_created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['instagram_message_id', 'source', 'sent_at']);
        });
    }
};
