<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Label: "Birthday", "Company", etc
            $table->string('key'); // snake_case: "birthday", "company"
            $table->enum('type', ['text', 'number', 'date', 'email', 'url', 'select', 'textarea'])->default('text');
            $table->json('options')->nullable(); // Untuk type select
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_custom_fields');
    }
};
