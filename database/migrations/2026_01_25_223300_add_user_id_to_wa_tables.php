<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration tambahan untuk Multi-Tenant Support
 * Menambahkan user_id ke tabel yang sebelumnya belum termasuk
 */
return new class extends Migration
{
    public function up(): void
    {
        // WaSession - tambah user_id
        if (Schema::hasTable('wa_sessions') && !Schema::hasColumn('wa_sessions', 'user_id')) {
            Schema::table('wa_sessions', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }

        // Instagram Accounts - sudah ada user_id, pastikan ada index
        if (Schema::hasTable('instagram_accounts') && Schema::hasColumn('instagram_accounts', 'user_id')) {
            Schema::table('instagram_accounts', function (Blueprint $table) {
                $table->index('user_id', 'instagram_accounts_user_id_idx');
            });
        }

        // WaConversation - tambah user_id
        if (Schema::hasTable('wa_conversations') && !Schema::hasColumn('wa_conversations', 'user_id')) {
            Schema::table('wa_conversations', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }

        // WaBroadcast - tambah user_id
        if (Schema::hasTable('wa_broadcasts') && !Schema::hasColumn('wa_broadcasts', 'user_id')) {
            Schema::table('wa_broadcasts', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        $tables = ['wa_sessions', 'wa_conversations', 'wa_broadcasts'];
        
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'user_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                });
            }
        }
    }
};
