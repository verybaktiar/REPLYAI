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
        // 1. Sequences
        if (!Schema::hasColumn('sequences', 'user_id')) {
            Schema::table('sequences', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            });
        }

        // 2. Sequence Steps
        if (!Schema::hasColumn('sequence_steps', 'user_id')) {
            Schema::table('sequence_steps', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            });
        }

        // 3. Sequence Enrollments
        if (!Schema::hasColumn('sequence_enrollments', 'user_id')) {
            Schema::table('sequence_enrollments', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            });
        }

        // 4. Quick Replies
        if (!Schema::hasColumn('quick_replies', 'user_id')) {
            Schema::table('quick_replies', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            });
        }

        // 5. Takeover Logs
        if (!Schema::hasColumn('takeover_logs', 'user_id')) {
            Schema::table('takeover_logs', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sequences', function (Blueprint $table) { $table->dropForeign(['user_id']); $table->dropColumn('user_id'); });
        Schema::table('sequence_steps', function (Blueprint $table) { $table->dropForeign(['user_id']); $table->dropColumn('user_id'); });
        Schema::table('sequence_enrollments', function (Blueprint $table) { $table->dropForeign(['user_id']); $table->dropColumn('user_id'); });
        Schema::table('quick_replies', function (Blueprint $table) { $table->dropForeign(['user_id']); $table->dropColumn('user_id'); });
        Schema::table('takeover_logs', function (Blueprint $table) { $table->dropForeign(['user_id']); $table->dropColumn('user_id'); });
    }
};
