<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk fix WaSession unique constraint untuk multi-tenant
 * Mengubah dari unique(session_id) ke unique(session_id, user_id)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_sessions', function (Blueprint $table) {
            // Drop unique constraint lama jika ada
            try {
                $table->dropUnique(['session_id']);
            } catch (\Exception $e) {
                // Constraint mungkin tidak ada, lanjutkan
            }
            
            // Buat composite unique key baru (session_id + user_id)
            $table->unique(['session_id', 'user_id'], 'wa_sessions_session_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('wa_sessions', function (Blueprint $table) {
            $table->dropUnique('wa_sessions_session_user_unique');
            $table->unique('session_id');
        });
    }
};
