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
        Schema::create('qa_test_results', function (Blueprint $table) {
            $table->id();
            $table->string('scenario_id');
            $table->enum('status', ['pass', 'fail', 'skip']);
            $table->text('notes')->nullable();
            $table->string('tested_by');
            $table->timestamp('tested_at');
            $table->timestamps();
            
            $table->index('scenario_id');
            $table->index('tested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_test_results');
    }
};
