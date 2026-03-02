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
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // unauthorized_admin_access, brute_force_attempt, etc
            $table->string('ip_address');
            $table->string('email_attempted')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->foreignId('resolved_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'is_resolved']);
            $table->index(['ip_address', 'created_at']);
            $table->index('created_at');
        });
        
        // Also improve admin_activity_logs table
        Schema::table('admin_activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_activity_logs', 'country_code')) {
                $table->string('country_code')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('admin_activity_logs', 'city')) {
                $table->string('city')->nullable()->after('country_code');
            }
            if (!Schema::hasColumn('admin_activity_logs', 'is_suspicious')) {
                $table->boolean('is_suspicious')->default(false)->after('city');
            }
            if (!Schema::hasColumn('admin_activity_logs', 'changes_before')) {
                $table->json('changes_before')->nullable()->after('details');
            }
            if (!Schema::hasColumn('admin_activity_logs', 'changes_after')) {
                $table->json('changes_after')->nullable()->after('changes_before');
            }
            if (!Schema::hasColumn('admin_activity_logs', 'risk_score')) {
                $table->tinyInteger('risk_score')->default(0)->after('is_suspicious');
            }
        });
        
        // Add 2FA columns to admin_users if not exists
        Schema::table('admin_users', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_users', 'two_factor_secret')) {
                $table->string('two_factor_secret')->nullable()->after('password');
            }
            if (!Schema::hasColumn('admin_users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            }
            if (!Schema::hasColumn('admin_users', 'two_factor_recovery_codes')) {
                $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
        
        Schema::table('admin_activity_logs', function (Blueprint $table) {
            $columns = ['country_code', 'city', 'is_suspicious', 'changes_before', 'changes_after', 'risk_score'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('admin_activity_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        
        Schema::table('admin_users', function (Blueprint $table) {
            $columns = ['two_factor_secret', 'two_factor_enabled', 'two_factor_recovery_codes'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('admin_users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
