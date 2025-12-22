<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auto_reply_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rule_id')->nullable()->constrained('auto_reply_rules')->nullOnDelete();

            $table->string('trigger_text')->nullable(); // isi dm yg memicu
            $table->text('response_text')->nullable();  // isi jawaban bot
            $table->enum('status', ['sent','skipped','failed'])->default('sent');
            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_reply_logs');
    }
};
