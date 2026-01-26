<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk Multi-Tenant Support
 * Menambahkan user_id ke semua tabel yang perlu difilter per user
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Conversations - sudah ada user_id, pastikan ada index
        if (Schema::hasColumn('conversations', 'user_id')) {
            Schema::table('conversations', function (Blueprint $table) {
                // Add index if not exists
                $table->index('user_id', 'conversations_user_id_index');
            });
        } else {
            Schema::table('conversations', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }

        // 2. KB Articles - tambah user_id
        if (!Schema::hasColumn('kb_articles', 'user_id')) {
            Schema::table('kb_articles', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }

        // 3. Auto Reply Rules - tambah user_id
        if (!Schema::hasColumn('auto_reply_rules', 'user_id')) {
            Schema::table('auto_reply_rules', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }

        // 4. Business Profiles - tambah user_id
        if (!Schema::hasColumn('business_profiles', 'user_id')) {
            Schema::table('business_profiles', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }

        // 5. Quick Replies - tambah user_id jika ada
        if (Schema::hasTable('quick_replies') && !Schema::hasColumn('quick_replies', 'user_id')) {
            Schema::table('quick_replies', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }

        // 6. Product Info - tambah user_id jika ada
        if (Schema::hasTable('product_infos') && !Schema::hasColumn('product_infos', 'user_id')) {
            Schema::table('product_infos', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }

        // 7. Operating Hours - tambah user_id jika ada
        if (Schema::hasTable('operating_hours') && !Schema::hasColumn('operating_hours', 'user_id')) {
            Schema::table('operating_hours', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        $tables = ['conversations', 'kb_articles', 'auto_reply_rules', 'business_profiles', 'quick_replies', 'product_infos', 'operating_hours'];
        
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
