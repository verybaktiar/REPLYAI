<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Nama widget/website
            $table->string('api_key')->unique();       // API key untuk autentikasi
            $table->string('domain')->nullable();      // Domain yang diizinkan (untuk CORS)
            $table->string('welcome_message')->default('Halo! Ada yang bisa kami bantu?');
            $table->string('bot_name')->default('Bot ReplyAI');
            $table->string('bot_avatar')->nullable();  // URL avatar bot
            $table->string('primary_color')->default('#4F46E5');
            $table->string('position')->default('bottom-right'); // bottom-right, bottom-left
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();      // Konfigurasi tambahan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_widgets');
    }
};
