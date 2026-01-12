<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk menyimpan langkah-langkah dalam sequence
     * Setiap sequence bisa punya banyak step (pesan) dengan delay masing-masing
     */
    public function up(): void
    {
        Schema::create('sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained()->onDelete('cascade');
            
            $table->unsignedInteger('order')->default(0); // Urutan step (0, 1, 2, ...)
            
            // Tipe delay: kapan pesan ini dikirim setelah step sebelumnya
            $table->enum('delay_type', ['immediately', 'minutes', 'hours', 'days'])->default('immediately');
            $table->unsignedInteger('delay_value')->default(0); // Nilai delay (misal: 5 menit, 2 jam, 1 hari)
            
            $table->text('message_content'); // Isi pesan yang akan dikirim
            
            $table->boolean('is_active')->default(true); // Status aktif step ini
            
            $table->timestamps();
            
            // Index untuk query performa
            $table->index(['sequence_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_steps');
    }
};
