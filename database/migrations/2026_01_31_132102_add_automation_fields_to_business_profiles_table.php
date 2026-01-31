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
            $table->boolean('enable_autofollowup')->default(false)->after('notification_settings');
            $table->text('followup_message')->nullable()->after('enable_autofollowup');
            $table->boolean('enable_daily_summary')->default(false)->after('followup_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn(['enable_autofollowup', 'followup_message', 'enable_daily_summary']);
        });
    }
};
