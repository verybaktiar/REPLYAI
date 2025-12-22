<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // bantu query "latest contact msg per conversation"
            $table->index(['conversation_id', 'sender_type', 'id'], 'msg_conv_sender_id_idx');

            // bantu order/filter pesan contact terbaru
            $table->index(['sender_type', 'message_created_at'], 'msg_sender_created_idx');
        });

        Schema::table('auto_reply_logs', function (Blueprint $table) {
            // bantu cek "udah pernah diproses belum"
            $table->index('message_id', 'arl_message_id_idx');

            // bantu cek cooldown / report
            $table->index(['conversation_id', 'response_source', 'status'], 'arl_conv_source_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('msg_conv_sender_id_idx');
            $table->dropIndex('msg_sender_created_idx');
        });

        Schema::table('auto_reply_logs', function (Blueprint $table) {
            $table->dropIndex('arl_message_id_idx');
            $table->dropIndex('arl_conv_source_status_idx');
        });
    }
};
