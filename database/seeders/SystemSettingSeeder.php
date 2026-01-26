<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Group: Features
            [
                'key' => 'enable_whatsapp',
                'value' => '1',
                'group' => 'features',
                'type' => 'boolean',
                'label' => 'Integrasi WhatsApp',
                'description' => 'Aktifkan atau matikan seluruh modul WhatsApp untuk semua user.',
            ],
            [
                'key' => 'enable_instagram',
                'value' => '1',
                'group' => 'features',
                'type' => 'boolean',
                'label' => 'Integrasi Instagram',
                'description' => 'Aktifkan atau matikan seluruh modul Instagram untuk semua user.',
            ],
            [
                'key' => 'enable_ai_simulator',
                'value' => '1',
                'group' => 'features',
                'type' => 'boolean',
                'label' => 'AI Simulator',
                'description' => 'Aktifkan atau matikan fitur uji coba AI Simulator.',
            ],
            [
                'key' => 'enable_broadcasts',
                'value' => '1',
                'group' => 'features',
                'type' => 'boolean',
                'label' => 'Modul Broadcast',
                'description' => 'Aktifkan atau matikan fitur pengiriman pesan massal.',
            ],

            // Group: Registration
            [
                'key' => 'enable_registration',
                'value' => '1',
                'group' => 'registration',
                'type' => 'boolean',
                'label' => 'Pendaftaran Baru',
                'description' => 'Jika dimatikan, pengunjung tidak bisa mendaftar akun baru.',
            ],
            [
                'key' => 'enable_trial',
                'value' => '1',
                'group' => 'registration',
                'type' => 'boolean',
                'label' => 'Trial 7 Hari',
                'description' => 'Berikan paket trial otomatis untuk setiap pendaftar baru.',
            ],

            // Group: Appearance
            [
                'key' => 'site_name',
                'value' => 'ReplyAI',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Nama Website',
                'description' => 'Nama platform yang muncul di judul halaman dan email.',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@replyai.com',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Email Support',
                'description' => 'Email tujuan untuk pertanyaan bantuan pelanggan.',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
