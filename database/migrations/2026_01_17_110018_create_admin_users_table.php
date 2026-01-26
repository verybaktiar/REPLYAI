<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabel Admin (Admin Users)
 * 
 * Tabel ini menyimpan data admin/super admin.
 * Admin login terpisah dari user biasa (pelanggan).
 * 
 * Level akses:
 * - superadmin : Akses penuh ke semua fitur
 * - support    : Bisa lihat tenant & handle tiket
 * - finance    : Bisa lihat revenue & approve pembayaran
 */
return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel
     */
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            // ID unik untuk setiap admin
            $table->id();
            
            // Nama lengkap admin
            $table->string('name');
            
            // Email untuk login (harus unik)
            $table->string('email')->unique();
            
            // Password (hashed)
            $table->string('password');
            
            /**
             * Role/peran admin:
             * - superadmin : Akses penuh, bisa apa saja
             * - support    : Handle tiket & lihat tenant
             * - finance    : Approve pembayaran & lihat revenue
             */
            $table->enum('role', ['superadmin', 'support', 'finance'])
                  ->default('support');
            
            // Foto profil (URL)
            $table->string('avatar')->nullable();
            
            // Nomor telepon (untuk notifikasi)
            $table->string('phone')->nullable();
            
            // Telegram chat ID (untuk notifikasi via Telegram)
            $table->string('telegram_chat_id')->nullable();
            
            // Apakah admin ini aktif
            $table->boolean('is_active')->default(true);
            
            // 2FA secret (untuk Google Authenticator)
            $table->string('two_factor_secret')->nullable();
            
            // Apakah 2FA sudah diaktifkan
            $table->boolean('two_factor_enabled')->default(false);
            
            // Token untuk remember me
            $table->rememberToken();
            
            // Terakhir login
            $table->timestamp('last_login_at')->nullable();
            
            // IP terakhir login
            $table->string('last_login_ip')->nullable();
            
            // Waktu dibuat dan diupdate
            $table->timestamps();
        });
        
        // Tabel untuk mencatat aktivitas admin
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Admin yang melakukan aksi
            $table->foreignId('admin_id')->constrained('admin_users')->onDelete('cascade');
            
            /**
             * Jenis aksi:
             * - login              : Admin login
             * - logout             : Admin logout
             * - approve_payment    : Approve pembayaran manual
             * - reject_payment     : Reject pembayaran
             * - suspend_tenant     : Suspend akun pelanggan
             * - activate_tenant    : Aktifkan akun pelanggan
             * - login_as           : Login ke akun pelanggan
             * - reply_ticket       : Balas tiket support
             * - close_ticket       : Tutup tiket
             * - update_plan        : Update paket
             * - create_promo       : Buat kode promo
             * - send_announcement  : Kirim pengumuman
             */
            $table->string('action');
            
            // Deskripsi singkat aksi
            $table->string('description')->nullable();
            
            // Detail tambahan (JSON)
            $table->json('details')->nullable();
            
            // Tipe target (model yang terkena aksi)
            $table->string('target_type')->nullable();
            
            // ID target
            $table->unsignedBigInteger('target_id')->nullable();
            
            // IP address saat melakukan aksi
            $table->string('ip_address')->nullable();
            
            // User agent browser
            $table->string('user_agent')->nullable();
            
            // Waktu dibuat
            $table->timestamp('created_at')->useCurrent();
            
            // Index untuk query cepat
            $table->index('admin_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Batalkan migration (hapus tabel)
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('admin_users');
    }
};
