<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk tracking user yang terdaftar dalam sequence
     * Menyimpan status dan progress setiap user dalam sequence
     */
    public function up(): void
    {
        Schema::create('sequence_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained()->onDelete('cascade');
            
            // Identifier kontak (bisa phone number, IG ID, atau web session)
            $table->string('contact_identifier'); // Misal: 628123456789 atau ig_user_123
            $table->string('contact_name')->nullable(); // Nama kontak (opsional)
            
            // Platform asal kontak
            $table->enum('platform', ['whatsapp', 'instagram', 'web'])->default('whatsapp');
            
            // Step saat ini (null = belum mulai atau sudah selesai)
            $table->foreignId('current_step_id')->nullable()->constrained('sequence_steps')->nullOnDelete();
            
            // Status enrollment
            // - active: sedang berjalan
            // - completed: sudah selesai semua step
            // - paused: dijeda sementara
            // - cancelled: dibatalkan
            $table->enum('status', ['active', 'completed', 'paused', 'cancelled'])->default('active');
            
            // Waktu-waktu penting
            $table->timestamp('enrolled_at')->useCurrent(); // Kapan user di-enroll
            $table->timestamp('completed_at')->nullable(); // Kapan selesai (jika sudah)
            $table->timestamp('next_run_at')->nullable(); // Kapan step selanjutnya dijadwalkan
            
            $table->timestamps();
            
            // Index untuk query scheduler
            $table->index(['status', 'next_run_at']);
            $table->index(['sequence_id', 'contact_identifier']);
            
            // Pastikan 1 kontak hanya bisa 1x active di 1 sequence
            $table->unique(['sequence_id', 'contact_identifier', 'platform'], 'unique_enrollment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_enrollments');
    }
};
