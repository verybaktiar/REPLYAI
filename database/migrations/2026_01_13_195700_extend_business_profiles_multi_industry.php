<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Extend business_profiles untuk mendukung multi-industri
     */
    public function up(): void
    {
        // Tambah kolom baru ke business_profiles
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->json('terminology')->nullable()->after('kb_fallback_message');
            $table->text('greeting_examples')->nullable()->after('terminology');
            $table->text('faq_examples')->nullable()->after('greeting_examples');
            $table->text('escalation_keywords')->nullable()->after('faq_examples');
            $table->string('industry_icon', 10)->nullable()->after('business_type');
        });
        
        // Note: business_type sudah string-like via enum, kita biarkan saja
        // Validasi tipe industri baru akan ditangani di level aplikasi (PHP)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'terminology',
                'greeting_examples',
                'faq_examples',
                'escalation_keywords',
                'industry_icon',
            ]);
        });
    }
};
