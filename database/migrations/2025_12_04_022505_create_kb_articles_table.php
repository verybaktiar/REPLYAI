<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kb_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('source_url')->nullable();
            $table->string('tags')->nullable(); // comma separated, optional
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // optional fulltext (kalau mysql support)
            // $table->fullText(['title','content','tags']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_articles');
    }
};
