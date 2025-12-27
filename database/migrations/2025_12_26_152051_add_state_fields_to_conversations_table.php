<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('waiting_for')->nullable()->index();
            $table->timestamp('greeted_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['waiting_for', 'greeted_at']);
        });
    }
};
