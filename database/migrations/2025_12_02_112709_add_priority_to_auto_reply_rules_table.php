<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_reply_rules', function (Blueprint $table) {
            // priority untuk urutan rule (semakin besar semakin diprioritaskan)
            $table->unsignedInteger('priority')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('auto_reply_rules', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
