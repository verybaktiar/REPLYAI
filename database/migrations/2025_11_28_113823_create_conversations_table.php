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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // ID conversation dari Chatwoot
            $table->unsignedBigInteger('chatwoot_id')->unique();

            // Info IG user
            $table->string('ig_username')->nullable()->index();  // contoh: @hawin_feri
            $table->string('display_name')->nullable();          // fallback name
            $table->string('avatar')->nullable();

            // summary terakhir
            $table->text('last_message')->nullable();
            $table->unsignedBigInteger('last_activity_at')->nullable()->index(); // timestamp chatwoot

            // status percakapan
            $table->string('status')->default('open')->index(); // open/resolved/pending

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
