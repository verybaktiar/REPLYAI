<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix auto_reply_rules table - add user_id if not exists
        if (!Schema::hasColumn('auto_reply_rules', 'user_id')) {
            Schema::table('auto_reply_rules', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            });
        }

        // Rename columns if using old schema (trigger -> trigger_keyword, reply -> response_text)
        if (Schema::hasColumn('auto_reply_rules', 'trigger') && !Schema::hasColumn('auto_reply_rules', 'trigger_keyword')) {
            Schema::table('auto_reply_rules', function (Blueprint $table) {
                $table->renameColumn('trigger', 'trigger_keyword');
            });
        }

        if (Schema::hasColumn('auto_reply_rules', 'reply') && !Schema::hasColumn('auto_reply_rules', 'response_text')) {
            Schema::table('auto_reply_rules', function (Blueprint $table) {
                $table->renameColumn('reply', 'response_text');
            });
        }

        // Add missing columns if not exists
        if (!Schema::hasColumn('auto_reply_rules', 'name')) {
            Schema::table('auto_reply_rules', function (Blueprint $table) {
                $table->string('name')->nullable()->after('user_id');
            });
        }

        if (!Schema::hasColumn('auto_reply_rules', 'priority')) {
            Schema::table('auto_reply_rules', function (Blueprint $table) {
                $table->integer('priority')->default(0)->after('response_text');
            });
        }

        // Fix quick_replies table - add missing columns
        if (!Schema::hasColumn('quick_replies', 'user_id')) {
            Schema::table('quick_replies', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('quick_replies', 'category')) {
            Schema::table('quick_replies', function (Blueprint $table) {
                $table->string('category')->nullable()->after('message');
            });
        }

        if (!Schema::hasColumn('quick_replies', 'usage_count')) {
            Schema::table('quick_replies', function (Blueprint $table) {
                $table->integer('usage_count')->default(0)->after('category');
            });
        }

        // Fix kb_articles table - add missing columns
        if (!Schema::hasColumn('kb_articles', 'user_id')) {
            Schema::table('kb_articles', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('kb_articles', 'business_profile_id')) {
            Schema::table('kb_articles', function (Blueprint $table) {
                $table->foreignId('business_profile_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('kb_articles', 'image_path')) {
            Schema::table('kb_articles', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('tags');
            });
        }

        // Fix chat_automations enum values to match model constants
        // Note: Changing enum values may require special handling
    }

    public function down(): void
    {
        // Revert changes
        Schema::table('auto_reply_rules', function (Blueprint $table) {
            if (Schema::hasColumn('auto_reply_rules', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('auto_reply_rules', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('auto_reply_rules', 'priority')) {
                $table->dropColumn('priority');
            }
        });

        Schema::table('quick_replies', function (Blueprint $table) {
            if (Schema::hasColumn('quick_replies', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('quick_replies', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('quick_replies', 'usage_count')) {
                $table->dropColumn('usage_count');
            }
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            if (Schema::hasColumn('kb_articles', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('kb_articles', 'business_profile_id')) {
                $table->dropForeign(['business_profile_id']);
                $table->dropColumn('business_profile_id');
            }
            if (Schema::hasColumn('kb_articles', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
