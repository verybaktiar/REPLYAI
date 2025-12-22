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
        Schema::table('messages', function (Blueprint $table) {
            // ubah sender_type jadi string biasa biar fleksibel
            $table->string('sender_type', 50)->nullable()->change();

            // sekalian kalau konten panjang, aman di text
            $table->text('content')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // rollback ke ukuran lama (sesuaikan kalau dulu enum)
            $table->string('sender_type', 20)->nullable()->change();
            $table->text('content')->nullable()->change();
        });
    }
};
