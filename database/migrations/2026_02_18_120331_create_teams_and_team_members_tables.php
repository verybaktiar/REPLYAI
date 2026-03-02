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
        // Teams table
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->index('owner_id');
        });

        // Team members pivot table
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('agent'); // owner, admin, manager, agent, viewer
            $table->json('permissions')->nullable();
            $table->timestamp('joined_at');
            $table->timestamps();
            
            $table->unique(['team_id', 'user_id']);
            $table->index('team_id');
            $table->index('user_id');
        });

        // Add team_id to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'current_team_id')) {
                $table->foreignId('current_team_id')->nullable()->after('id')->constrained('teams')->nullOnDelete();
            }
        });

        // Add team_id to conversations
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
                $table->foreignId('assigned_to')->nullable()->after('team_id')->constrained('users')->nullOnDelete();
            }
        });

        // Add team_id to other relevant tables
        $tables = ['wa_conversations', 'web_conversations', 'kb_articles', 'auto_reply_rules', 'chat_automations'];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'team_id')) {
                        $table->foreignId('team_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'current_team_id')) {
                $table->dropForeign(['current_team_id']);
                $table->dropColumn('current_team_id');
            }
        });

        $tables = ['conversations', 'wa_conversations', 'web_conversations', 'kb_articles', 'auto_reply_rules', 'chat_automations'];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'team_id')) {
                        $table->dropForeign(['team_id']);
                        $table->dropColumn('team_id');
                    }
                    if ($tableName === 'conversations' && Schema::hasColumn($tableName, 'assigned_to')) {
                        $table->dropForeign(['assigned_to']);
                        $table->dropColumn('assigned_to');
                    }
                });
            }
        }

        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }
};
