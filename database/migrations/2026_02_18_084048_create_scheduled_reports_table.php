<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('report_type'); // analytics, ai_performance, csat, conversation_quality
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();
            $table->integer('day_of_month')->nullable(); // 1-31
            $table->time('send_time')->default('09:00:00');
            $table->string('email_to');
            $table->enum('format', ['pdf', 'excel', 'csv'])->default('pdf');
            $table->json('filters')->nullable(); // { platform: 'all', date_range: 30, metrics: [...] }
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('next_send_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active', 'next_send_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
