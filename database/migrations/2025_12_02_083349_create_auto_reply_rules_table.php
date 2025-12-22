<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auto_reply_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // nama rule, contoh: "Layanan RS"
            $table->string('trigger_keyword');       // kata pemicu, contoh: "pelayanan"
            $table->text('response_text');           // jawaban otomatis
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // kalau banyak rule, pilih yg priority paling tinggi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_reply_rules');
    }
};
