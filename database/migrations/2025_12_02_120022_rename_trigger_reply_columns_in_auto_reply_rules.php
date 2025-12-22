<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_reply_rules', function (Blueprint $table) {
            // rename trigger -> trigger_keyword
            if (Schema::hasColumn('auto_reply_rules', 'trigger') &&
                !Schema::hasColumn('auto_reply_rules', 'trigger_keyword')) {
                $table->renameColumn('trigger', 'trigger_keyword');
            }

            // rename reply -> response_text
            if (Schema::hasColumn('auto_reply_rules', 'reply') &&
                !Schema::hasColumn('auto_reply_rules', 'response_text')) {
                $table->renameColumn('reply', 'response_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('auto_reply_rules', function (Blueprint $table) {
            if (Schema::hasColumn('auto_reply_rules', 'trigger_keyword')) {
                $table->renameColumn('trigger_keyword', 'trigger');
            }

            if (Schema::hasColumn('auto_reply_rules', 'response_text')) {
                $table->renameColumn('response_text', 'reply');
            }
        });
    }
};
