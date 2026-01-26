<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

/**
 * Seeder: Paket Langganan Default
 * 
 * Seeder ini membuat 4 paket langganan default:
 * 1. Gratis    - Untuk UMKM yang baru mulai
 * 2. Hemat     - Untuk toko online & reseller
 * 3. Pro       - Untuk bisnis menengah
 * 4. Enterprise - Untuk perusahaan besar
 * 
 * Cara menjalankan:
 * php artisan db:seed --class=PlanSeeder
 */
class PlanSeeder extends Seeder
{
    /**
     * Jalankan seeder
     */
    public function run(): void
    {
        // ==========================================
        // PAKET GRATIS (Starter)
        // ==========================================
        Plan::updateOrCreate(
            ['slug' => 'gratis'],
            [
                'name' => 'Gratis',
                'description' => 'Cocok untuk UMKM yang baru mulai. Coba semua fitur dasar tanpa biaya.',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'features' => [
                    // Batasan kuantitas
                    'wa_devices' => 1,          // 1 WhatsApp device
                    'contacts' => 100,           // 100 kontak
                    'ai_messages' => 50,         // 50 pesan AI per bulan
                    'kb_storage' => 5242880,     // 5 MB (dalam bytes)
                    'quick_replies' => 5,        // 5 template
                    'team_members' => 1,         // 1 orang
                    
                    // Akses fitur (true/false)
                    'broadcasts' => 0,           // Tidak bisa broadcast
                    'sequences' => 0,            // Tidak bisa sequence
                    'web_widgets' => 0,          // Tidak bisa web widget
                    'analytics_export' => false, // Tidak bisa export
                    'remove_branding' => false,  // Ada "Powered by ReplyAI"
                    'api_access' => false,       // Tidak bisa akses API
                    
                    // Analytics
                    'analytics_days' => 7,       // Data 7 hari terakhir
                ],
                'is_active' => true,
                'sort_order' => 1,
                'is_free' => true,
                'is_trial' => false,
                'trial_days' => 0,
            ]
        );

        // ==========================================
        // PAKET HEMAT (Starter Plus)
        // ==========================================
        Plan::updateOrCreate(
            ['slug' => 'hemat'],
            [
                'name' => 'Hemat',
                'description' => 'Semua fitur penting untuk bisnis online. Cocok untuk toko online & reseller.',
                'price_monthly' => 99000,        // Rp 99.000/bulan
                'price_yearly' => 990000,        // Rp 990.000/tahun (hemat 2 bulan)
                'features' => [
                    // Batasan kuantitas
                    'wa_devices' => 2,           // 2 WhatsApp device
                    'contacts' => 1000,          // 1.000 kontak
                    'ai_messages' => 500,        // 500 pesan AI per bulan
                    'kb_storage' => 52428800,    // 50 MB
                    'quick_replies' => 20,       // 20 template
                    'team_members' => 2,         // 2 orang
                    
                    // Akses fitur
                    'broadcasts' => 500,         // 500 broadcast per bulan
                    'sequences' => 3,            // 3 sequence aktif
                    'web_widgets' => 0,          // Tidak bisa web widget
                    'analytics_export' => false, // Tidak bisa export
                    'remove_branding' => false,  // Ada "Powered by ReplyAI"
                    'api_access' => false,       // Tidak bisa akses API
                    
                    // Analytics
                    'analytics_days' => 30,      // Data 30 hari
                ],
                'is_active' => true,
                'sort_order' => 2,
                'is_free' => false,
                'is_trial' => false,
                'trial_days' => 0,
            ]
        );

        // ==========================================
        // PAKET PRO (Business)
        // ==========================================
        Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'description' => 'Untuk bisnis yang serius scale up. Fitur lengkap untuk bisnis menengah.',
                'price_monthly' => 249000,       // Rp 249.000/bulan
                'price_yearly' => 2490000,       // Rp 2.490.000/tahun
                'features' => [
                    // Batasan kuantitas
                    'wa_devices' => 5,           // 5 WhatsApp device
                    'contacts' => 5000,          // 5.000 kontak
                    'ai_messages' => 2000,       // 2.000 pesan AI per bulan
                    'kb_storage' => 209715200,   // 200 MB
                    'quick_replies' => 50,       // 50 template
                    'team_members' => 5,         // 5 orang
                    
                    // Akses fitur
                    'broadcasts' => 5000,        // 5.000 broadcast per bulan
                    'sequences' => 10,           // 10 sequence aktif
                    'web_widgets' => 3,          // 3 web widget
                    'analytics_export' => true,  // Bisa export CSV
                    'remove_branding' => true,   // Tanpa "Powered by ReplyAI"
                    'api_access' => true,        // Bisa akses API
                    
                    // Analytics
                    'analytics_days' => 90,      // Data 90 hari
                ],
                'is_active' => true,
                'sort_order' => 3,
                'is_free' => false,
                'is_trial' => false,
                'trial_days' => 0,
            ]
        );

        // ==========================================
        // PAKET ENTERPRISE (Custom)
        // ==========================================
        Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'Solusi lengkap untuk enterprise. Unlimited semua fitur + dedicated support.',
                'price_monthly' => 500000,       // Mulai Rp 500.000/bulan
                'price_yearly' => 5000000,       // Rp 5.000.000/tahun
                'features' => [
                    // Batasan kuantitas (-1 = unlimited)
                    'wa_devices' => -1,          // Unlimited
                    'contacts' => -1,            // Unlimited
                    'ai_messages' => -1,         // Unlimited
                    'kb_storage' => -1,          // Unlimited
                    'quick_replies' => -1,       // Unlimited
                    'team_members' => -1,        // Unlimited
                    
                    // Akses fitur
                    'broadcasts' => -1,          // Unlimited
                    'sequences' => -1,           // Unlimited
                    'web_widgets' => -1,         // Unlimited
                    'analytics_export' => true,
                    'remove_branding' => true,
                    'api_access' => true,
                    
                    // Analytics
                    'analytics_days' => 365,     // Data 1 tahun
                    
                    // Bonus enterprise
                    'priority_support' => true,
                    'sla' => true,
                    'custom_integration' => true,
                ],
                'is_active' => true,
                'sort_order' => 4,
                'is_free' => false,
                'is_trial' => false,
                'trial_days' => 0,
            ]
        );

        // ==========================================
        // PAKET TRIAL (Hidden - untuk user baru)
        // ==========================================
        Plan::updateOrCreate(
            ['slug' => 'trial'],
            [
                'name' => 'Trial Pro',
                'description' => 'Coba semua fitur Pro selama 7 hari gratis!',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'features' => [
                    // Sama dengan Pro
                    'wa_devices' => 5,
                    'contacts' => 5000,
                    'ai_messages' => 2000,
                    'kb_storage' => 209715200,
                    'quick_replies' => 50,
                    'team_members' => 5,
                    'broadcasts' => 5000,
                    'sequences' => 10,
                    'web_widgets' => 3,
                    'analytics_export' => true,
                    'remove_branding' => false,
                    'api_access' => true,
                    'analytics_days' => 90,
                ],
                'is_active' => true,
                'sort_order' => 0,
                'is_free' => true,
                'is_trial' => true,
                'trial_days' => 7,
            ]
        );

        $this->command->info('✅ Paket langganan berhasil dibuat!');
        $this->command->table(
            ['Slug', 'Nama', 'Harga/Bulan'],
            Plan::orderBy('sort_order')->get(['slug', 'name', 'price_monthly'])->toArray()
        );
    }
}
