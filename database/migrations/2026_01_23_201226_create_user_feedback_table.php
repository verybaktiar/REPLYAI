<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['bug', 'feature', 'improvement', 'other'])->default('feature');
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['new', 'reviewed', 'planned', 'in_progress', 'done', 'declined'])->default('new');
            $table->integer('votes')->default(0);
            $table->text('admin_response')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};
