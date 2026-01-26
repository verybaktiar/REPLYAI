<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabel Pembayaran (Payments)
 * 
 * Tabel ini menyimpan riwayat semua pembayaran.
 * Setiap kali user bayar (baru atau perpanjang), akan dibuat record baru.
 * 
 * Alur pembayaran:
 * 1. User pilih paket & klik bayar
 * 2. System buat record payment dengan status 'pending'
 * 3. User transfer/bayar via gateway
 * 4. Admin approve atau gateway webhook update status jadi 'paid'
 * 5. Langganan user diaktifkan
 */
return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            // ID unik untuk setiap pembayaran
            $table->id();
            
            // Nomor invoice yang unik (contoh: INV-2024-00001)
            $table->string('invoice_number')->unique();
            
            // User yang bayar (relasi ke tabel users)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Langganan terkait (relasi ke tabel subscriptions)
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            
            // Paket yang dibeli (relasi ke tabel plans)
            $table->foreignId('plan_id')->constrained()->onDelete('restrict');
            
            // Jumlah yang harus dibayar (dalam Rupiah)
            $table->integer('amount');
            
            // Diskon (jika ada promo code)
            $table->integer('discount')->default(0);
            
            // Jumlah akhir setelah diskon
            $table->integer('total');
            
            /**
             * Metode pembayaran:
             * - manual_transfer : Transfer bank manual (perlu approval admin)
             * - midtrans        : Via Midtrans (otomatis)
             * - xendit          : Via Xendit (otomatis)
             */
            $table->string('payment_method')->default('manual_transfer');
            
            /**
             * Status pembayaran:
             * - pending   : Menunggu pembayaran
             * - paid      : Sudah dibayar & dikonfirmasi
             * - failed    : Gagal/dibatalkan
             * - refunded  : Sudah direfund
             */
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])
                  ->default('pending');
            
            // Referensi dari payment gateway (contoh: order_id dari Midtrans)
            $table->string('payment_reference')->nullable();
            
            // URL bukti transfer (untuk manual transfer)
            $table->string('proof_url')->nullable();
            
            // Tanggal dibayar/dikonfirmasi
            $table->timestamp('paid_at')->nullable();
            
            // Tanggal expired (jika tidak bayar dalam X jam, auto cancel)
            $table->timestamp('expires_at')->nullable();
            
            // Durasi langganan yang dibeli (dalam bulan: 1 atau 12)
            $table->integer('duration_months')->default(1);
            
            // Kode promo yang digunakan (jika ada)
            $table->string('promo_code')->nullable();
            
            // Detail tambahan (dalam format JSON)
            $table->json('metadata')->nullable();
            
            // Catatan dari admin
            $table->text('admin_notes')->nullable();
            
            // Admin yang approve (untuk manual transfer)
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Waktu dibuat dan diupdate
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index(['user_id', 'status']);
            $table->index('invoice_number');
        });
    }

    /**
     * Batalkan migration (hapus tabel)
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
