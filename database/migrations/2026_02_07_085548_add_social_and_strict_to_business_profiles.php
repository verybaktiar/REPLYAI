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
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->string('instagram_handle')->nullable()->after('business_type');
            $table->string('website_url')->nullable()->after('instagram_handle');
            $table->text('address_map_url')->nullable()->after('website_url');
            $table->boolean('strict_mode')->default(false)->after('is_active');
            $table->string('primary_language')->default('id')->after('strict_mode');
            $table->integer('ai_msg_daily_limit')->default(100)->after('primary_language'); // Safety cap
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'instagram_handle',
                'website_url',
                'address_map_url',
                'strict_mode',
                'primary_language',
                'ai_msg_daily_limit'
            ]);
        });
    }
};
