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
            // Change business_type from ENUM to STRING to support dynamic industries
            $table->string('business_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM if needed (warning: data loss if values don't match enum)
        // For safety, we keep it as string in down or try to revert to a wider enum.
        // But strictly adhering to original:
        // Schema::table('business_profiles', function (Blueprint $table) {
        //    $table->enum('business_type', ['hospital', 'retail', 'service', 'general'])->change();
        // });
        
        // Better to just leave it as string in down() to avoid data loss, 
        // or re-define enum with ALL values if we really wanted to.
    }
};
