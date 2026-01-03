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
        Schema::create('takeover_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['whatsapp', 'instagram']);
            $table->string('conversation_id'); // phone_number for WA, id for IG
            $table->string('customer_name')->nullable();
            $table->enum('action', ['takeover', 'handback', 'auto_handback', 'cs_reply']);
            $table->string('actor')->default('system'); // CS name or 'system'
            $table->integer('idle_duration_minutes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['platform', 'created_at']);
            $table->index('conversation_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('takeover_logs');
    }
};
