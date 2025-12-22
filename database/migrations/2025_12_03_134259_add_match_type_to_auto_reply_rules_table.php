<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('auto_reply_rules', function (Blueprint $table) {
            $table
                ->string('match_type', 20)
                ->default('contains')
                ->after('trigger_keyword'); 
            // kalau trigger_keyword gak ada di schema kamu, ganti after ke kolom yang ada.
        });
    }

    public function down(): void
    {
        Schema::table('auto_reply_rules', function (Blueprint $table) {
            $table->dropColumn('match_type');
        });
    }
};
