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
        // Add foreign key constraint if both tables exist and FK doesn't exist
        if (Schema::hasTable('contact_field_values') && Schema::hasTable('contact_custom_fields')) {
            Schema::table('contact_field_values', function (Blueprint $table) {
                // Check if foreign key exists
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreignKeys = $sm->listTableForeignKeys('contact_field_values');
                $hasForeignKey = false;
                
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('field_id', $foreignKey->getLocalColumns())) {
                        $hasForeignKey = true;
                        break;
                    }
                }
                
                if (!$hasForeignKey) {
                    $table->foreign('field_id')
                        ->references('id')
                        ->on('contact_custom_fields')
                        ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('contact_field_values')) {
            Schema::table('contact_field_values', function (Blueprint $table) {
                $table->dropForeign(['field_id']);
            });
        }
    }
};
