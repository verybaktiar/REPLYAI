<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            // $table->enum('role', ['super_admin', 'admin', 'support'])->default('admin')->after('email'); // Sudah ada
            $table->json('permissions')->nullable()->after('email'); // Geser ke after email karena role sudah ada
        });
    }

    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn(['permissions']);
        });
    }
};
