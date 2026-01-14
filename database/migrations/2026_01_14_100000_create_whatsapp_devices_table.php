<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_devices', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique(); // ID unik untuk sesi Baileys (misal: 'admin-1', 'cs-team')
            $table->string('device_name'); // Nama label perangkat (misal: 'HP Admin Utama')
            $table->string('phone_number')->nullable(); // Nomor WA yang terhubung
            $table->string('profile_name')->nullable(); // Nama profil WA
            $table->enum('status', ['connected', 'disconnected', 'scanning', 'unknown'])->default('unknown');
            $table->text('last_disconnect_reason')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_devices');
    }
};
