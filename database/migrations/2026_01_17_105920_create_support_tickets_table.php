<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabel Tiket Support (Support Tickets)
 * 
 * Tabel ini menyimpan tiket bantuan dari pelanggan.
 * Ketika pelanggan ada masalah, mereka bisa submit tiket.
 * Admin akan dapat notifikasi dan bisa membalas.
 * 
 * Alur tiket:
 * 1. Pelanggan submit tiket (status: open)
 * 2. Admin melihat dan mulai handle (status: in_progress)
 * 3. Admin balas, menunggu feedback pelanggan (status: waiting_customer)
 * 4. Masalah selesai (status: resolved)
 * 5. Tiket ditutup (status: closed)
 */
return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel
     */
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            // ID unik untuk setiap tiket
            $table->id();
            
            // Nomor tiket yang unik (contoh: TKT-2024-00001)
            $table->string('ticket_number')->unique();
            
            // Pelanggan yang submit (relasi ke tabel users)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            /**
             * Kategori masalah:
             * - bot_not_responding : Bot tidak merespon
             * - whatsapp_issue     : Masalah WhatsApp (disconnect, dll)
             * - payment            : Masalah pembayaran
             * - feature_bug        : Bug/fitur tidak berfungsi
             * - feature_request    : Permintaan fitur baru
             * - other              : Lainnya
             */
            $table->string('category');
            
            // Judul/subjek tiket
            $table->string('subject');
            
            // Pesan/deskripsi masalah
            $table->text('message');
            
            // Lampiran (JSON array of URLs)
            $table->json('attachments')->nullable();
            
            /**
             * Prioritas tiket:
             * - low    : Tidak urgent
             * - medium : Perlu ditangani segera
             * - high   : Penting, harus cepat ditangani
             * - urgent : Sangat penting, harus segera
             */
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                  ->default('medium');
            
            /**
             * Status tiket:
             * - open             : Baru dibuat, belum ditangani
             * - in_progress      : Sedang ditangani admin
             * - waiting_customer : Menunggu balasan dari pelanggan
             * - resolved         : Sudah diselesaikan
             * - closed           : Ditutup
             */
            $table->enum('status', ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'])
                  ->default('open');
            
            // Admin yang menangani (relasi ke tabel users/admin_users)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Tanggal diselesaikan
            $table->timestamp('resolved_at')->nullable();
            
            // Tanggal ditutup
            $table->timestamp('closed_at')->nullable();
            
            // Rating dari pelanggan setelah resolved (1-5)
            $table->tinyInteger('rating')->nullable();
            
            // Feedback dari pelanggan
            $table->text('feedback')->nullable();
            
            // Waktu dibuat dan diupdate
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('priority');
        });
        
        // Tabel untuk balasan tiket
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            
            // Tiket yang dibalas
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            
            /**
             * Tipe pengirim:
             * - customer : Pelanggan yang reply
             * - admin    : Admin/support yang reply
             * - system   : Pesan otomatis dari sistem
             */
            $table->enum('sender_type', ['customer', 'admin', 'system']);
            
            // ID pengirim (user_id)
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Isi balasan
            $table->text('message');
            
            // Lampiran (JSON array of URLs)
            $table->json('attachments')->nullable();
            
            // Apakah sudah dibaca oleh penerima
            $table->boolean('is_read')->default(false);
            
            // Waktu dibuat
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index('ticket_id');
        });
    }

    /**
     * Batalkan migration (hapus tabel)
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('support_tickets');
    }
};
