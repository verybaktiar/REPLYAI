<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kb_scrape_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_profile_id')->nullable()->constrained()->onDelete('set null');
            
            // Job status
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('job_id')->unique(); // UUID for tracking
            
            // URL and source
            $table->text('url');
            $table->string('target_name')->nullable(); // Nama KB yang diinginkan
            
            // Progress tracking
            $table->integer('progress_percent')->default(0);
            $table->string('progress_step')->nullable(); // Step description
            
            // Results
            $table->json('scraped_data')->nullable(); // Hasil ekstraksi
            $table->json('extracted_entities')->nullable(); // Entitas terdeteksi
            $table->text('error_message')->nullable();
            
            // Raw HTML for debugging (auto-delete after 24h via scheduled command)
            $table->longText('raw_html')->nullable();
            
            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['job_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_scrape_jobs');
    }
};
