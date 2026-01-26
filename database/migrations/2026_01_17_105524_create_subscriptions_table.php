<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabel Langganan (Subscriptions)
 * 
 * Tabel ini menyimpan data langganan setiap user/tenant.
 * Satu user hanya memiliki satu langganan aktif pada satu waktu.
 * 
 * Alur langganan:
 * 1. User daftar → otomatis dapat paket Gratis
 * 2. User upgrade → pilih paket berbayar & bayar
 * 3. Langganan aktif sampai tanggal expires_at
 * 4. Jika tidak diperpanjang → status jadi 'expired'
 */
return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            // ID unik untuk setiap langganan
            $table->id();
            
            // User/Tenant yang berlangganan (relasi ke tabel users)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Paket yang dipilih (relasi ke tabel plans)
            $table->foreignId('plan_id')->constrained()->onDelete('restrict');
            
            /**
             * Status langganan:
             * - trial     : Sedang dalam masa percobaan gratis
             * - active    : Langganan aktif (sudah bayar)
             * - past_due  : Pembayaran terlambat (dalam grace period)
             * - canceled  : Dibatalkan oleh user
             * - expired   : Sudah habis masa berlaku
             */
            $table->enum('status', ['trial', 'active', 'past_due', 'canceled', 'expired'])
                  ->default('trial');
            
            // Tanggal mulai langganan
            $table->timestamp('starts_at')->nullable();
            
            // Tanggal berakhir langganan (harus perpanjang sebelum tanggal ini)
            $table->timestamp('expires_at')->nullable();
            
            // Tanggal trial berakhir (jika ada trial)
            $table->timestamp('trial_ends_at')->nullable();
            
            // Tanggal dibatalkan (jika user cancel)
            $table->timestamp('canceled_at')->nullable();
            
            // Tanggal grace period berakhir (setelah expired, user masih bisa akses X hari)
            $table->timestamp('grace_period_ends_at')->nullable();
            
            /**
             * Metode pembayaran terakhir:
             * - manual_transfer : Transfer bank manual
             * - midtrans       : Via Midtrans
             * - xendit         : Via Xendit
             */
            $table->string('payment_method')->nullable();
            
            // Catatan internal (untuk admin)
            $table->text('notes')->nullable();
            
            // Waktu dibuat dan diupdate
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Batalkan migration (hapus tabel)
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
