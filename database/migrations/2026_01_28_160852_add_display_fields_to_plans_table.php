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
        Schema::table('plans', function (Blueprint $table) {
            $table->string('price_monthly_display')->nullable()->after('price_monthly_original');
            $table->string('price_monthly_original_display')->nullable()->after('price_monthly_display');
            $table->string('price_yearly_display')->nullable()->after('price_yearly_original');
            $table->string('price_yearly_original_display')->nullable()->after('price_yearly_display');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'price_monthly_display',
                'price_monthly_original_display',
                'price_yearly_display',
                'price_yearly_original_display'
            ]);
        });
    }
};
