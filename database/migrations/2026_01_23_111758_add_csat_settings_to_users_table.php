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
        Schema::table('users', function (Blueprint $table) {
            // CSAT Settings
            $table->boolean('csat_enabled')->default(false)->after('is_vip');
            $table->boolean('csat_instagram_enabled')->default(true)->after('csat_enabled');
            $table->boolean('csat_whatsapp_enabled')->default(true)->after('csat_instagram_enabled');
            $table->string('csat_message', 500)->nullable()->after('csat_whatsapp_enabled'); // Custom message
            $table->integer('csat_delay_minutes')->default(5)->after('csat_message'); // Delay before sending CSAT
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'csat_enabled',
                'csat_instagram_enabled',
                'csat_whatsapp_enabled',
                'csat_message',
                'csat_delay_minutes',
            ]);
        });
    }
};
