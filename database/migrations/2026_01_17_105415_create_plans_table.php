<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabel Paket Langganan (Plans)
 * 
 * Tabel ini menyimpan daftar paket langganan yang tersedia.
 * Contoh: Gratis, Hemat, Pro, Enterprise
 * 
 * Setiap paket memiliki:
 * - Nama dan deskripsi
 * - Harga bulanan dan tahunan
 * - Batasan fitur (dalam format JSON)
 */
return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            // ID unik untuk setiap paket
            $table->id();
            
            // Nama paket (contoh: "Hemat", "Pro", "Enterprise")
            $table->string('name');
            
            // Slug untuk URL (contoh: "hemat", "pro", "enterprise")
            $table->string('slug')->unique();
            
            // Deskripsi singkat paket
            $table->text('description')->nullable();
            
            // Harga bulanan dalam Rupiah (contoh: 99000 untuk Rp 99.000)
            $table->integer('price_monthly')->default(0);
            
            // Harga tahunan dalam Rupiah (biasanya ada diskon)
            $table->integer('price_yearly')->default(0);
            
            // Batasan fitur dalam format JSON
            // Contoh: {"whatsapp_devices": 2, "contacts": 1000, "ai_messages": 500, ...}
            $table->json('features')->nullable();
            
            // Apakah paket ini aktif dan bisa dipilih user
            $table->boolean('is_active')->default(true);
            
            // Urutan tampilan (untuk sorting di halaman pricing)
            $table->integer('sort_order')->default(0);
            
            // Apakah ini paket gratis (default untuk user baru)
            $table->boolean('is_free')->default(false);
            
            // Apakah ini paket trial
            $table->boolean('is_trial')->default(false);
            
            // Durasi trial dalam hari (jika is_trial = true)
            $table->integer('trial_days')->default(0);
            
            // Waktu dibuat dan diupdate
            $table->timestamps();
        });
    }

    /**
     * Batalkan migration (hapus tabel)
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
