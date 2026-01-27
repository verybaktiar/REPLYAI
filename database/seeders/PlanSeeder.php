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
        // Deaktifkan semua paket lama agar tidak muncul di UI
        Plan::query()->update(['is_active' => false]);

        // ==========================================
        // PAKET PRO (UMKM & Bisnis Baru)
        // ==========================================
        Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'description' => 'Untuk UMKM & bisnis yang mulai otomatisasi chat',
                'price_monthly' => 500000,
                'price_monthly_original' => 1100000,
                'price_yearly' => 5000000,
                'price_yearly_original' => 11000000,
                'features' => [
                    'wa_devices' => 1,
                    'contacts' => 5000,
                    'ai_messages' => 1000,
                    'kb_articles' => 100,
                    'auto_reply_rules' => 50,
                    'ai_reply' => true,
                    'knowledge_base' => true,
                    'rules_management' => true,
                    'unified_inbox' => true,
                    'takeover' => true,
                    'quick_reply' => true,
                ],
                'features_list' => [
                    '1 Saluran (Pilih: WhatsApp, Instagram, atau Facebook)',
                    'Kotak Masuk Terpadu',
                    'Balasan Otomatis Berbasis Kata Kunci',
                    'Balasan AI Dasar',
                    'Pesan Cepat untuk CS',
                    'Pengalihan Chat ke CS (Manual)',
                    'Dasbor & Statistik Dasar',
                    'Manajemen Kontak (Tag Terbatas)',
                    'Dukungan Email',
                ],
                'is_active' => true,
                'sort_order' => 1,
                'is_free' => false,
                'is_trial' => false,
            ]
        );

        // ==========================================
        // PAKET BUSINESS (Bisnis & Tim CS)
        // ==========================================
        Plan::updateOrCreate(
            ['slug' => 'business'],
            [
                'name' => 'Business',
                'description' => 'Paling populer untuk bisnis & tim CS',
                'price_monthly' => 1500000,
                'price_monthly_original' => 2500000,
                'price_yearly' => 15000000,
                'price_yearly_original' => 25000000,
                'features' => [
                    'wa_devices' => -1,
                    'contacts' => -1,
                    'ai_messages' => 5000,
                    'kb_articles' => -1,
                    'auto_reply_rules' => -1,
                    'ai_reply' => true,
                    'knowledge_base' => true,
                    'rules_management' => true,
                    'unified_inbox' => true,
                    'takeover' => true,
                    'quick_reply' => true,
                    'analytics' => true,
                    'api_access' => true,
                    'sequences' => true,
                ],
                'features_list' => [
                    'Semua fitur Paket Pro',
                    'Semua Saluran + Website',
                    'Balasan Otomatis Tanpa Batas',
                    'Basis Pengetahuan (Dokumen & Website)',
                    'Balasan AI Lanjutan',
                    'Pesan Bertahap Otomatis',
                    'Pengalihan & Kembali Otomatis ke Bot',
                    'Manajemen Kontak Tanpa Batas',
                    'Analitik & Laporan Lengkap',
                    'Banyak Pengguna CS',
                    'Integrasi Sistem (API)',
                    'Sistem Tiket Bantuan',
                ],
                'is_active' => true,
                'sort_order' => 2,
                'is_free' => false,
                'is_trial' => false,
            ]
        );

        // ==========================================
        // PAKET ENTERPRISE (Perusahaan & Institusi)
        // ==========================================
        Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'Untuk perusahaan & institusi skala besar',
                'price_monthly' => 3500000,
                'price_monthly_original' => 4800000,
                'price_yearly' => 35000000,
                'price_yearly_original' => 48000000,
                'features' => [
                    'wa_devices' => -1,
                    'contacts' => -1,
                    'ai_messages' => -1,
                    'kb_articles' => -1,
                    'auto_reply_rules' => -1,
                    'broadcasts' => 5000,
                    'ai_reply' => true,
                    'knowledge_base' => true,
                    'rules_management' => true,
                    'unified_inbox' => true,
                    'takeover' => true,
                    'quick_reply' => true,
                    'analytics' => true,
                    'api_access' => true,
                    'sequences' => true,
                    'priority_support' => true,
                    'multi_business' => true,
                    'activity_logs' => true,
                ],
                'features_list' => [
                    'Semua fitur Paket Business',
                    'Kirim Pesan Massal (Limit 5.000/bln)',
                    'Semua Saluran + SMS & Email',
                    'Banyak Profil Bisnis',
                    'AI Khusus per Industri',
                    'Alur Otomatis Sesuai SOP',
                    'Hak Akses & Peran Lanjutan',
                    'Log Aktivitas Lengkap',
                    'Pendampingan & Pelatihan Tim',
                    'Dukungan Prioritas (SLA)',
                    'Integrasi Sistem Internal',
                ],
                'is_active' => true,
                'sort_order' => 3,
                'is_free' => false,
                'is_trial' => false,
            ]
        );

        // ==========================================
        // PAKET CUSTOM (Solusi Khusus)
        // ==========================================
        Plan::updateOrCreate(
            ['slug' => 'custom'],
            [
                'name' => 'Custom',
                'description' => 'Solusi khusus sesuai kebutuhan bisnis',
                'price_monthly' => 0, // Dihandle via text "Custom" di view bila 0
                'is_active' => true,
                'sort_order' => 4,
                'is_free' => false,
                'features' => [],
                'features_list' => [
                    'Fitur Sesuai Permintaan',
                    'White Label (Tanpa Branding ReplyAI)',
                    'Server Khusus',
                    'Integrasi Sistem Khusus',
                    'AI Dilatih Data Internal',
                    'Dukungan Teknis Dedicated',
                ]
            ]
        );

        // ==========================================
        // PAKET GRATIS (Starter)
        // ==========================================
        Plan::updateOrCreate(
            ['slug' => 'gratis'],
            [
                'name' => 'Gratis',
                'description' => 'Versi starter untuk uji coba sistem',
                'price_monthly' => 0,
                'is_active' => false,
                'sort_order' => 0,
                'is_free' => true,
                'features' => [
                    'wa_devices' => 1,
                    'contacts' => 50,
                    'ai_messages' => 20,
                    'kb_articles' => 5,
                    'auto_reply_rules' => 3,
                ],
                'features_list' => [
                    '1 WhatsApp Device',
                    '50 Kontak',
                    '20 Pesan AI per bulan',
                ]
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
                    'kb_articles' => 100,
                    'auto_reply_rules' => 50,
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
