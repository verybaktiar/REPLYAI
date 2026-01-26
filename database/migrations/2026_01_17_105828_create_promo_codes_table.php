<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabel Kode Promo (Promo Codes)
 * 
 * Tabel ini menyimpan daftar kode promo/kupon diskon.
 * User bisa memasukkan kode saat checkout untuk dapat diskon.
 * 
 * Contoh penggunaan:
 * - Kode "LAUNCH50" = diskon 50% untuk 100 orang pertama
 * - Kode "HEMAT20K" = potongan Rp 20.000
 */
return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel
     */
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            // ID unik untuk setiap kode promo
            $table->id();
            
            // Kode promo (contoh: LAUNCH50, HEMAT20K) - harus unik dan uppercase
            $table->string('code')->unique();
            
            // Deskripsi kode promo (untuk internal)
            $table->string('description')->nullable();
            
            /**
             * Tipe diskon:
             * - percent : Diskon persentase (contoh: 50%)
             * - fixed   : Potongan nominal (contoh: Rp 20.000)
             */
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            
            // Nilai diskon (50 untuk 50% atau 20000 untuk Rp 20.000)
            $table->integer('discount_value');
            
            // Maksimum diskon untuk tipe percent (opsional)
            // Contoh: Diskon 50% maksimal Rp 100.000
            $table->integer('max_discount')->nullable();
            
            // Minimum pembelian untuk bisa pakai kode ini
            $table->integer('min_purchase')->default(0);
            
            // Berapa kali kode ini sudah dipakai
            $table->integer('usage_count')->default(0);
            
            // Batas maksimal penggunaan (null = unlimited)
            $table->integer('usage_limit')->nullable();
            
            // Tanggal mulai berlaku
            $table->timestamp('valid_from')->nullable();
            
            // Tanggal berakhir
            $table->timestamp('valid_until')->nullable();
            
            // Paket yang bisa pakai kode ini (JSON array of plan_ids, null = semua)
            $table->json('applicable_plans')->nullable();
            
            // Apakah kode ini aktif
            $table->boolean('is_active')->default(true);
            
            // Apakah hanya untuk user baru
            $table->boolean('new_users_only')->default(false);
            
            // Apakah hanya bisa dipakai sekali per user
            $table->boolean('single_use_per_user')->default(true);
            
            // Waktu dibuat dan diupdate
            $table->timestamps();
        });
        
        // Tabel untuk track siapa saja yang sudah pakai kode promo
        Schema::create('promo_code_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('discount_amount'); // Jumlah diskon yang didapat
            $table->timestamps();
            
            // Pastikan 1 user hanya bisa pakai 1 kode 1 kali (jika single_use_per_user = true)
            $table->unique(['promo_code_id', 'user_id']);
        });
    }

    /**
     * Batalkan migration (hapus tabel)
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_code_usages');
        Schema::dropIfExists('promo_codes');
    }
};
