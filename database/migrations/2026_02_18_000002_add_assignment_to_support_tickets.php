<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            // Admin assignment - check if not exists
            if (!Schema::hasColumn('support_tickets', 'assigned_admin_id')) {
                $table->foreignId('assigned_admin_id')->nullable()->after('user_id')
                      ->constrained('admin_users')->nullOnDelete();
            }
            
            // SLA tracking - check if not exists
            if (!Schema::hasColumn('support_tickets', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable()->after('assigned_admin_id');
            }
            if (!Schema::hasColumn('support_tickets', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('first_response_at');
            }
            if (!Schema::hasColumn('support_tickets', 'sla_breach_minutes')) {
                $table->integer('sla_breach_minutes')->nullable()->after('resolved_at');
            }
            
            // Internal notes - check if not exists
            if (!Schema::hasColumn('support_tickets', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('message');
            }
            
            // CSAT - check if not exists
            if (!Schema::hasColumn('support_tickets', 'rating')) {
                $table->tinyInteger('rating')->nullable()->after('status');
            }
            if (!Schema::hasColumn('support_tickets', 'feedback')) {
                $table->text('feedback')->nullable()->after('rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'assigned_admin_id')) {
                $table->dropForeign(['assigned_admin_id']);
            }
            
            $columnsToDrop = [];
            if (Schema::hasColumn('support_tickets', 'assigned_admin_id')) $columnsToDrop[] = 'assigned_admin_id';
            if (Schema::hasColumn('support_tickets', 'first_response_at')) $columnsToDrop[] = 'first_response_at';
            if (Schema::hasColumn('support_tickets', 'resolved_at')) $columnsToDrop[] = 'resolved_at';
            if (Schema::hasColumn('support_tickets', 'sla_breach_minutes')) $columnsToDrop[] = 'sla_breach_minutes';
            if (Schema::hasColumn('support_tickets', 'internal_notes')) $columnsToDrop[] = 'internal_notes';
            if (Schema::hasColumn('support_tickets', 'rating')) $columnsToDrop[] = 'rating';
            if (Schema::hasColumn('support_tickets', 'feedback')) $columnsToDrop[] = 'feedback';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
