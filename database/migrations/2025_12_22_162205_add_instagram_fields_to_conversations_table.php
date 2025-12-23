<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Cek dulu apakah kolom belum ada
            if (!Schema::hasColumn('conversations', 'instagram_user_id')) {
                $table->string('instagram_user_id')->nullable()->after('id');
            }
            
            if (!Schema::hasColumn('conversations', 'instagram_username')) {
                $table->string('instagram_username')->nullable()->after('instagram_user_id');
            }
            
            if (!Schema::hasColumn('conversations', 'last_message')) {
                $table->text('last_message')->nullable()->after('instagram_username');
            }
            
            if (!Schema::hasColumn('conversations', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->after('last_message');
            }
            
            if (!Schema::hasColumn('conversations', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('last_message_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn([
                'instagram_user_id',
                'instagram_username', 
                'last_message',
                'last_message_at',
                'is_read'
            ]);
        });
    }
};
