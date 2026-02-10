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
        Schema::create('kb_missed_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_profile_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('question');
            $table->integer('count')->default(1)->comment('Berapa kali pertanyaan ini ditanyakan');
            $table->string('status')->default('pending')->comment('pending, ignored, resolved');
            $table->foreignId('resolved_by_kb_id')->nullable()->constrained('kb_articles')->onDelete('set null')
                ->comment('KB article yang menjawab pertanyaan ini setelah resolved');
            $table->timestamp('last_asked_at')->useCurrent();
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kb_missed_queries');
    }
};
