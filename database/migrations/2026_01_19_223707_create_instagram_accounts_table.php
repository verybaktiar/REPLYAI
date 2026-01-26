<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instagram_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('instagram_user_id')->unique();
            $table->string('username')->nullable();
            $table->string('name')->nullable();
            $table->string('profile_picture_url', 500)->nullable();
            $table->string('page_id')->nullable(); // Facebook Page ID if using Page-based auth
            $table->text('access_token');
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('instagram_user_id');
            $table->index('user_id');
        });

        // Add user_id to conversations table for multi-tenancy
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::dropIfExists('instagram_accounts');
    }
};
