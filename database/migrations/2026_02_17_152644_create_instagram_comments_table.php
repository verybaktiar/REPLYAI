<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instagram_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('instagram_account_id')->constrained()->onDelete('cascade');
            $table->string('instagram_comment_id')->unique();
            $table->string('media_id'); // Post/story ID
            $table->string('from_username');
            $table->string('from_id');
            $table->text('text');
            $table->string('parent_comment_id')->nullable(); // Untuk reply
            $table->boolean('is_replied')->default(false);
            $table->text('reply_text')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('commented_at');
            $table->timestamps();
            
            $table->index(['user_id', 'is_replied']);
            $table->index(['instagram_account_id', 'commented_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instagram_comments');
    }
};
