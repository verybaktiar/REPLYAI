<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class UmkmPlansSeeder extends Seeder
{
    /**
     * Seed UMKM-focused plans
     */
    public function run(): void
    {
        // 1. GRATIS - UMKM Starter
        Plan::updateOrCreate(
            ['slug' => 'gratis'],
            [
                'name' => 'Gratis',
                'description' => 'Paket dasar untuk UMKM memulai bisnis online',
                'price_monthly' => 0,
                'price_monthly_original' => 0,
                'price_monthly_display' => 'Gratis',
                'price_yearly' => 0,
                'price_yearly_original' => 0,
                'price_yearly_display' => 'Gratis',
                'features' => [
                    'whatsapp_devices' => 1,
                    'instagram_accounts' => 1,
                    'contacts' => 500,
                    'kb_articles' => 50,
                    'auto_reply_rules' => 10,
                    'quick_replies' => 20,
                    'ai_messages_monthly' => 500,
                    'broadcasts_monthly' => 0, // Tidak ada broadcast untuk UMKM
                    'sequences' => false,
                    'web_widgets' => false,
                    'api_access' => false,
                    'team_members' => 1,
                    'segment' => false,
                    'chat_automation' => false,
                    'advanced_reports' => false,
                    'instagram_comments' => false,
                ],
                'features_list' => [
                    'WhatsApp & Instagram Integration',
                    '500 Contacts',
                    '50 Knowledge Base Articles',
                    '10 Auto Reply Rules',
                    '20 Quick Replies',
                    '500 AI Messages/month',
                    'Basic Statistics',
                    '7-day History',
                ],
                'is_active' => true,
                'sort_order' => 1,
                'is_free' => true,
                'is_trial' => false,
                'tier' => 'umkm',
            ]
        );

        // 2. HEMAT - UMKM Growth
        Plan::updateOrCreate(
            ['slug' => 'hemat'],
            [
                'name' => 'Hemat',
                'description' => 'Paket terjangkau untuk UMKM yang mulai berkembang',
                'price_monthly' => 99000,
                'price_monthly_original' => 99000,
                'price_monthly_display' => '99rb',
                'price_yearly' => 999000,
                'price_yearly_original' => 1188000,
                'price_yearly_display' => '999rb',
                'features' => [
                    'whatsapp_devices' => 1,
                    'instagram_accounts' => 1,
                    'contacts' => 1000,
                    'kb_articles' => 100,
                    'auto_reply_rules' => 20,
                    'quick_replies' => 50,
                    'ai_messages_monthly' => 2000,
                    'broadcasts_monthly' => 0, // Masih tidak ada broadcast
                    'sequences' => false,
                    'web_widgets' => false,
                    'api_access' => false,
                    'team_members' => 1,
                    'segment' => false,
                    'chat_automation' => false,
                    'advanced_reports' => false,
                    'instagram_comments' => false,
                ],
                'features_list' => [
                    'Semua fitur Gratis',
                    '1,000 Contacts',
                    '100 Knowledge Base Articles',
                    '20 Auto Reply Rules',
                    '50 Quick Replies',
                    '2,000 AI Messages/month',
                    'Priority Support',
                ],
                'is_active' => true,
                'sort_order' => 2,
                'is_free' => false,
                'is_trial' => false,
                'tier' => 'umkm',
            ]
        );

        // Update existing plans tier
        Plan::where('slug', 'pro')->update(['tier' => 'business', 'sort_order' => 3]);
        Plan::where('slug', 'business')->update(['tier' => 'business', 'sort_order' => 4]);
        Plan::where('slug', 'enterprise')->update(['tier' => 'enterprise', 'sort_order' => 5]);
        Plan::where('slug', 'custom')->update(['tier' => 'enterprise', 'sort_order' => 6]);
    }
}
