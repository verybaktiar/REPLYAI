<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Ubah tipe kolom jadi TIMESTAMP atau DATETIME
            $table->timestamp('last_activity_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->change();
        });
    }
};
