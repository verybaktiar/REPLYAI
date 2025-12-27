<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'has_sent_welcome')) {
                $table->boolean('has_sent_welcome')->default(false)->after('status');
            }
            if (!Schema::hasColumn('conversations', 'last_menu_sent_at')) {
                $table->timestamp('last_menu_sent_at')->nullable()->after('has_sent_welcome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'has_sent_welcome')) {
                $table->dropColumn('has_sent_welcome');
            }
            if (Schema::hasColumn('conversations', 'last_menu_sent_at')) {
                $table->dropColumn('last_menu_sent_at');
            }
        });
    }
};
