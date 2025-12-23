<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Tambah kolom untuk Meta API (nullable agar data lama tidak error)
            if (!Schema::hasColumn('conversations', 'instagram_user_id')) {
                $table->string('instagram_user_id')->nullable()->after('chatwoot_id');
            }
            
            if (!Schema::hasColumn('conversations', 'source')) {
                $table->enum('source', ['chatwoot', 'meta_direct'])->default('chatwoot')->after('instagram_user_id');
            }
            
            if (!Schema::hasColumn('conversations', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('updated_at');
            }
            
            if (!Schema::hasColumn('conversations', 'status')) {
                $table->string('status')->default('open')->after('last_activity_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['instagram_user_id', 'source', 'last_activity_at', 'status']);
        });
    }
};
