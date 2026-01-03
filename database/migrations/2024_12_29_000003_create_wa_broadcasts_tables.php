<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('media_path')->nullable();
            $table->enum('status', ['draft', 'processing', 'completed', 'canceled'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->json('filters')->nullable(); // Stores criteria used to select targets
            $table->timestamps();
        });

        Schema::create('wa_broadcast_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_broadcast_id')->constrained('wa_broadcasts')->onDelete('cascade');
            $table->string('phone_number');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['wa_broadcast_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_broadcast_targets');
        Schema::dropIfExists('wa_broadcasts');
    }
};
