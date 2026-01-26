<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk menambahkan user_id ke wa_messages
 * Diperlukan untuk multi-tenant data isolation
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wa_messages') && !Schema::hasColumn('wa_messages', 'user_id')) {
            Schema::table('wa_messages', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wa_messages') && Schema::hasColumn('wa_messages', 'user_id')) {
            Schema::table('wa_messages', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
