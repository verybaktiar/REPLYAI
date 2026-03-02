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
        // Check if table already exists
        if (Schema::hasTable('contact_field_values')) {
            return;
        }

        Schema::create('contact_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('contact_type'); // whatsapp, instagram, web
            $table->string('contact_id'); // phone_number, instagram_user_id, visitor_id
            $table->unsignedBigInteger('field_id'); // Custom field ID
            $table->text('value')->nullable();
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['user_id', 'contact_type', 'contact_id']);
            $table->index(['field_id']);
            
            // Prevent duplicate values for same field
            $table->unique(['user_id', 'contact_type', 'contact_id', 'field_id'], 'unique_field_value');
        });

        // Add foreign key separately to handle potential table creation order issues
        if (Schema::hasTable('contact_custom_fields')) {
            Schema::table('contact_field_values', function (Blueprint $table) {
                $table->foreign('field_id')
                    ->references('id')
                    ->on('contact_custom_fields')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_field_values');
    }
};
