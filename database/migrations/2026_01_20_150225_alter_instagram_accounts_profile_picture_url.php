<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instagram_accounts', function (Blueprint $table) {
            // Change profile_picture_url to TEXT to accommodate long CDN URLs
            $table->text('profile_picture_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('instagram_accounts', function (Blueprint $table) {
            $table->string('profile_picture_url', 500)->nullable()->change();
        });
    }
};
