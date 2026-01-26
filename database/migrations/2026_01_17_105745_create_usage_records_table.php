<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabel Pencatatan Penggunaan (Usage Records)
 * 
 * Tabel ini mencatat berapa banyak fitur yang sudah digunakan user.
 * Digunakan untuk membatasi penggunaan sesuai paket langganan.
 * 
 * Contoh penggunaan:
 * - User paket Hemat punya limit 500 pesan AI/bulan
 * - Setiap kali AI menjawab, counter bertambah
 * - Jika sudah 500, user tidak bisa pakai AI lagi (harus upgrade)
 * - Counter direset setiap awal bulan
 */
return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel
     */
    public function up(): void
    {
        Schema::create('usage_records', function (Blueprint $table) {
            // ID unik untuk setiap record
            $table->id();
            
            // User yang ditrack (relasi ke tabel users)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            /**
             * Jenis fitur yang ditrack:
             * - ai_messages    : Jumlah pesan AI yang dikirim
             * - broadcasts     : Jumlah broadcast yang dikirim
             * - contacts       : Jumlah kontak tersimpan (tidak reset)
             * - kb_storage     : Ukuran knowledge base dalam bytes (tidak reset)
             * - sequences      : Jumlah sequence aktif (tidak reset)
             * - quick_replies  : Jumlah quick reply (tidak reset)
             * - web_widgets    : Jumlah web widget (tidak reset)
             * - wa_devices     : Jumlah WhatsApp device (tidak reset)
             * - team_members   : Jumlah anggota tim (tidak reset)
             */
            $table->string('feature_key');
            
            // Jumlah yang sudah digunakan
            $table->integer('used_count')->default(0);
            
            // Tanggal mulai periode (untuk fitur yang reset bulanan)
            $table->date('period_start')->nullable();
            
            // Tanggal akhir periode
            $table->date('period_end')->nullable();
            
            // Apakah ini fitur yang reset bulanan
            $table->boolean('resets_monthly')->default(true);
            
            // Waktu terakhir diupdate
            $table->timestamps();
            
            // Pastikan 1 user hanya punya 1 record per fitur per periode
            $table->unique(['user_id', 'feature_key', 'period_start']);
            
            // Index untuk query cepat
            $table->index(['user_id', 'feature_key']);
        });
    }

    /**
     * Batalkan migration (hapus tabel)
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_records');
    }
};
