<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('wa_broadcasts', 'user_id')) {
            Schema::table('wa_broadcasts', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('wa_broadcast_targets', 'user_id')) {
            Schema::table('wa_broadcast_targets', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_broadcasts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('wa_broadcast_targets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
