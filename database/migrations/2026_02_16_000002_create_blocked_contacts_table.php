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
        Schema::create('blocked_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('contact_type'); // whatsapp, instagram, web
            $table->string('contact_id'); // phone_number, instagram_user_id, visitor_id
            $table->text('reason')->nullable();
            $table->timestamp('blocked_at');
            $table->foreignId('blocked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['user_id', 'contact_type', 'contact_id']);
            $table->index(['user_id', 'blocked_at']);
            
            // Prevent duplicate blocks
            $table->unique(['user_id', 'contact_type', 'contact_id'], 'unique_blocked_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_contacts');
    }
};
