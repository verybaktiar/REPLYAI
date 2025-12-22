<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_reply_rules', function (Blueprint $table) {

            // kalau masih ada kolom lama trigger/reply -> rename ke kolom baru
            if (Schema::hasColumn('auto_reply_rules', 'trigger') && !Schema::hasColumn('auto_reply_rules', 'trigger_keyword')) {
                $table->renameColumn('trigger', 'trigger_keyword');
            }

            if (Schema::hasColumn('auto_reply_rules', 'reply') && !Schema::hasColumn('auto_reply_rules', 'response_text')) {
                $table->renameColumn('reply', 'response_text');
            }

            // kalau kolom baru belum ada -> tambah
            if (!Schema::hasColumn('auto_reply_rules', 'trigger_keyword')) {
                $table->string('trigger_keyword')->nullable()->after('id');
            }

            if (!Schema::hasColumn('auto_reply_rules', 'response_text')) {
                $table->text('response_text')->nullable()->after('trigger_keyword');
            }

            // pastikan kolom control ada
            if (!Schema::hasColumn('auto_reply_rules', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('response_text');
            }

            if (!Schema::hasColumn('auto_reply_rules', 'priority')) {
                $table->integer('priority')->default(0)->after('is_active');
            }
        });

        // backfill data dari kolom lama kalau ada yg null
        if (
            Schema::hasColumn('auto_reply_rules', 'trigger_keyword') &&
            Schema::hasColumn('auto_reply_rules', 'response_text')
        ) {
            DB::table('auto_reply_rules')
                ->whereNull('trigger_keyword')
                ->update(['trigger_keyword' => DB::raw('trigger_keyword')]);

            DB::table('auto_reply_rules')
                ->whereNull('response_text')
                ->update(['response_text' => DB::raw('response_text')]);
        }
    }

    public function down(): void
    {
        // rollback minimal (ga rename balik untuk jaga data)
        Schema::table('auto_reply_rules', function (Blueprint $table) {
            // sengaja dikosongkan biar aman
        });
    }
};
