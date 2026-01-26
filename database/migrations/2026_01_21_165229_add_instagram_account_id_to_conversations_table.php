<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom instagram_account_id untuk menghubungkan
     * conversation dengan akun Instagram bisnis yang spesifik.
     * Ini penting untuk multi-tenancy ketika user mengganti akun IG.
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'instagram_account_id')) {
                $table->unsignedBigInteger('instagram_account_id')->nullable()->after('user_id');
                $table->index('instagram_account_id');
                
                // Foreign key constraint
                $table->foreign('instagram_account_id')
                    ->references('id')
                    ->on('instagram_accounts')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'instagram_account_id')) {
                $table->dropForeign(['instagram_account_id']);
                $table->dropIndex(['instagram_account_id']);
                $table->dropColumn('instagram_account_id');
            }
        });
    }
};
