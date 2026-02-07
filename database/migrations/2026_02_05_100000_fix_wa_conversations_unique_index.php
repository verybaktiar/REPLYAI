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
        Schema::table('wa_conversations', function (Blueprint $table) {
            // Drop the old unique index on phone_number
            $table->dropUnique('wa_conversations_phone_number_unique');
            
            // Add a new composite unique index on phone_number and user_id
            $table->unique(['phone_number', 'user_id'], 'wa_conversations_phone_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->dropUnique('wa_conversations_phone_user_unique');
            $table->unique('phone_number', 'wa_conversations_phone_number_unique');
        });
    }
};
