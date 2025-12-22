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
        Schema::create('bot_rules', function (Blueprint $table) {
            $table->id();

            // keyword/pattern trigger
            $table->string('keyword')->index();     // contoh: "pelayanan"
            $table->enum('match_type', ['contains', 'exact', 'regex'])
                  ->default('contains');

            // balasan bot
            $table->text('reply_text');

            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(0)->index(); // makin besar makin diprioritaskan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_rules');
    }
};