<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel utama untuk menyimpan data sequence/drip campaign
     */
    public function up(): void
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama sequence, misal: "Welcome Series"
            $table->text('description')->nullable(); // Deskripsi sequence
            
            // Tipe trigger: kapan sequence dimulai
            // - manual: admin enroll manual
            // - first_message: saat user pertama kali chat
            // - keyword: saat user mengetik keyword tertentu
            // - tag_added: saat tag ditambahkan ke kontak
            $table->enum('trigger_type', ['manual', 'first_message', 'keyword', 'tag_added'])->default('manual');
            $table->string('trigger_value')->nullable(); // Nilai trigger (keyword/tag name)
            
            // Platform target: whatsapp, instagram, web, atau semua
            $table->enum('platform', ['whatsapp', 'instagram', 'web', 'all'])->default('all');
            
            $table->boolean('is_active')->default(true); // Status aktif/nonaktif
            
            // Statistik
            $table->unsignedInteger('total_enrolled')->default(0); // Total user yang pernah di-enroll
            $table->unsignedInteger('total_completed')->default(0); // Total user yang selesai semua step
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
