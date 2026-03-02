<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum column to include all types
        // Using raw SQL for enum modification
        if (Schema::hasTable('contact_custom_fields')) {
            // For MySQL, we need to modify the enum
            $connection = DB::connection()->getDriverName();
            
            if ($connection === 'mysql') {
                DB::statement("ALTER TABLE contact_custom_fields 
                    MODIFY COLUMN type ENUM('text', 'number', 'date', 'email', 'url', 'select', 'multi_select', 'textarea', 'checkbox') 
                    NOT NULL DEFAULT 'text'");
            } else {
                // For other databases, just use string
                Schema::table('contact_custom_fields', function (Blueprint $table) {
                    $table->string('type', 50)->default('text')->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        if (Schema::hasTable('contact_custom_fields')) {
            $connection = DB::connection()->getDriverName();
            
            if ($connection === 'mysql') {
                DB::statement("ALTER TABLE contact_custom_fields 
                    MODIFY COLUMN type ENUM('text', 'number', 'date', 'email', 'url', 'select', 'textarea') 
                    NOT NULL DEFAULT 'text'");
            }
        }
    }
};
