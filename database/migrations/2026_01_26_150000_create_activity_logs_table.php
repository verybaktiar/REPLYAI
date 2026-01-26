<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk Activity Logs
 * Mencatat semua aktivitas penting di sistem
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Who did the action
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            
            // What action
            $table->string('action', 100); // 'user.login', 'kb.created', etc
            $table->text('description')->nullable();
            
            // On what entity
            $table->string('model_type', 100)->nullable(); // App\Models\KbArticle
            $table->unsignedBigInteger('model_id')->nullable();
            
            // Additional context
            $table->json('properties')->nullable(); // old/new values, metadata
            
            // Request context
            $table->string('ip_address', 50)->nullable();
            $table->text('user_agent')->nullable();
            
            // Flags
            $table->boolean('is_impersonated')->default(false);
            $table->string('severity', 20)->default('info'); // info, warning, error
            
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index('user_id');
            $table->index('admin_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
