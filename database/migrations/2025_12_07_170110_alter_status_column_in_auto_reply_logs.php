<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_reply_logs', function (Blueprint $table) {
            // ubah jadi varchar panjang biar bebas status baru
            $table->string('status', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('auto_reply_logs', function (Blueprint $table) {
            // balik ke default (kalau dulu string pendek)
            $table->string('status', 20)->change();
        });
    }
};
