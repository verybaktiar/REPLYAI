<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: Admin User Default
 * 
 * Seeder ini membuat akun admin default untuk pertama kali.
 * 
 * Default credentials:
 * Email: admin@replyai.com
 * Password: Admin123!
 * 
 * ⚠️ PENTING: Ganti password ini setelah login pertama kali!
 * 
 * Cara menjalankan:
 * php artisan db:seed --class=AdminSeeder
 */
class AdminSeeder extends Seeder
{
    /**
     * Jalankan seeder
     */
    public function run(): void
    {
        // Buat Super Admin default
        AdminUser::updateOrCreate(
            ['email' => 'admin@replyai.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin123!'), // GANTI PASSWORD INI!
                'role' => AdminUser::ROLE_SUPERADMIN,
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Admin user berhasil dibuat!');
        $this->command->newLine();
        $this->command->warn('⚠️  PENTING: Ganti password default setelah login!');
        $this->command->newLine();
        $this->command->table(
            ['Email', 'Password', 'Role'],
            [
                ['admin@replyai.com', 'Admin123!', 'Super Admin'],
            ]
        );
        $this->command->newLine();
        $this->command->info('🔗 Login di: ' . url('/superadmin/login'));
    }
}
