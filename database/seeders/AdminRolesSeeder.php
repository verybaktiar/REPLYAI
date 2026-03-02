<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: Admin Roles Demo
 * 
 * Seeder ini membuat akun admin dengan berbagai role untuk testing:
 * - superadmin@replyai.com (Super Admin - full access)
 * - finance@replyai.com (Finance - payments & revenue only)
 * - support@replyai.com (Support - tickets & view only)
 * 
 * ⚠️ PENTING: Password harus diganti setelah login pertama kali!
 * 
 * Cara menjalankan:
 * php artisan db:seed --class=AdminRolesSeeder
 */
class AdminRolesSeeder extends Seeder
{
    /**
     * Jalankan seeder
     */
    public function run(): void
    {
        // Super Admin (Full Access)
        AdminUser::updateOrCreate(
            ['email' => 'superadmin@replyai.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuperAdmin123!'),
                'role' => AdminUser::ROLE_SUPERADMIN,
                'is_active' => true,
            ]
        );

        // Finance Admin (Payments & Revenue only)
        AdminUser::updateOrCreate(
            ['email' => 'finance@replyai.com'],
            [
                'name' => 'Finance Admin',
                'password' => Hash::make('Finance123!'),
                'role' => AdminUser::ROLE_FINANCE,
                'is_active' => true,
            ]
        );

        // Support Admin (Tickets & View only)
        AdminUser::updateOrCreate(
            ['email' => 'support@replyai.com'],
            [
                'name' => 'Support Admin',
                'password' => Hash::make('Support123!'),
                'role' => AdminUser::ROLE_SUPPORT,
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Admin users dengan role berhasil dibuat!');
        $this->command->newLine();
        $this->command->warn('⚠️  PENTING: Ganti password default setelah login!');
        $this->command->newLine();
        $this->command->table(
            ['Email', 'Password', 'Role', 'Akses'],
            [
                ['superadmin@replyai.com', 'SuperAdmin123!', 'Super Admin', 'Full Access'],
                ['finance@replyai.com', 'Finance123!', 'Finance', 'Payments & Revenue'],
                ['support@replyai.com', 'Support123!', 'Support', 'Tickets & View Only'],
            ]
        );
        $this->command->newLine();
        $this->command->info('🔐 Login di: ' . url('/admin/login'));
    }
}
