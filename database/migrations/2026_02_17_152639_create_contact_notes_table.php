<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Owner
            $table->morphs('contact'); // Polymorphic: bisa wa_messages (by phone) atau conversations (IG)
            $table->text('content');
            $table->string('category')->default('general'); // general, complaint, feedback, order
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['contact_type', 'contact_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_notes');
    }
};
