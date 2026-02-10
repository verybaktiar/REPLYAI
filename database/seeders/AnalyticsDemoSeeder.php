<?php

namespace Database\Seeders;

use App\Models\KbArticle;
use App\Models\KbMissedQuery;
use App\Models\WaMessage;
use App\Models\WhatsAppDevice;
use App\Models\BusinessProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AnalyticsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->warn('No user found. Please run UserSeeder first.');
            return;
        }

        $profile = BusinessProfile::where('user_id', $user->id)->first();
        if (!$profile) {
            $this->command->warn('No business profile found for user.');
            return;
        }

        $device = WhatsAppDevice::where('user_id', $user->id)->first();
        if (!$device) {
            $this->command->warn('No WhatsApp device found for user.');
            return;
        }

        $this->command->info('Creating demo analytics data...');

        // Create sample KB articles if none exist
        if (KbArticle::where('user_id', $user->id)->count() === 0) {
            $this->createSampleKbArticles($user->id, $profile->id);
        }

        // Create sample missed queries
        $this->createSampleMissedQueries($user->id, $profile->id);

        // Create sample WhatsApp messages
        $this->createSampleMessages($device->id, $device->session_id);

        $this->command->info('Demo analytics data created successfully!');
    }

    private function createSampleKbArticles(int $userId, int $profileId): void
    {
        $articles = [
            [
                'title' => 'Informasi Harga Paket',
                'content' => "📦 Paket Starter\n   • Rp 99.000/bulan\n   • 1 WhatsApp number\n   • 500 AI responses\n\n📦 Paket Business\n   • Rp 299.000/bulan\n   • 3 WhatsApp numbers\n   • Unlimited AI responses\n   • Priority support",
                'category' => 'pricing',
            ],
            [
                'title' => 'Cara Setup WhatsApp',
                'content' => "1. Buka menu Integrasi & Profil\n2. Klik WhatsApp Connect\n3. Scan QR code dengan WhatsApp Anda\n4. Tunggu sampai status 'connected'\n\nJika ada masalah, hubungi support kami.",
                'category' => 'tutorial',
            ],
            [
                'title' => 'Jam Operasional',
                'content' => "🕐 Senin - Jumat: 08:00 - 17:00\n🕐 Sabtu: 09:00 - 15:00\n🕐 Minggu & Hari Libur: Tutup\n\nAI Assistant aktif 24/7 untuk menjawab pertanyaan dasar.",
                'category' => 'general',
            ],
            [
                'title' => 'Cara Pembayaran',
                'content' => "Kami menerima pembayaran via:\n   • Transfer Bank (BCA, Mandiri, BNI)\n   • GoPay\n   • OVO\n   • QRIS\n\nInvoice akan dikirim otomatis ke email Anda.",
                'category' => 'payment',
            ],
        ];

        foreach ($articles as $article) {
            KbArticle::create([
                'user_id' => $userId,
                'business_profile_id' => $profileId,
                'title' => $article['title'],
                'content' => $article['content'],
                'category' => $article['category'],
                'is_active' => true,
            ]);
        }
    }

    private function createSampleMissedQueries(int $userId, int $profileId): void
    {
        $queries = [
            ['question' => 'Apakah ada diskon untuk pembelian tahunan?', 'count' => 12],
            ['question' => 'Bisa integrasi dengan Shopify?', 'count' => 8],
            ['question' => 'Bagaimana cara reset password?', 'count' => 6],
            ['question' => 'Apakah support bahasa Inggris?', 'count' => 5],
            ['question' => 'Bisa digunakan untuk multiple lokasi?', 'count' => 4],
            ['question' => 'Apakah ada API untuk developer?', 'count' => 3],
            ['question' => 'Bagaimana cara export data chat?', 'count' => 3],
            ['question' => 'Apakah bisa digunakan di iPad?', 'count' => 2],
        ];

        foreach ($queries as $query) {
            KbMissedQuery::create([
                'user_id' => $userId,
                'business_profile_id' => $profileId,
                'question' => $query['question'],
                'count' => $query['count'],
                'status' => 'pending',
            ]);
        }
    }

    private function createSampleMessages(int $deviceId, string $sessionId): void
    {
        // Create messages for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);
            $messageCount = rand(3, 15);

            for ($j = 0; $j < $messageCount; $j++) {
                $hasReply = rand(1, 10) <= 7; // 70% have replies
                
                WaMessage::create([
                    'device_id' => $deviceId,
                    'session_id' => $sessionId,
                    'remote_jid' => '628' . rand(100000000, 999999999) . '@s.whatsapp.net',
                    'message' => $this->getRandomQuestion(),
                    'direction' => 'incoming',
                    'is_from_me' => false,
                    'is_read' => true,
                    'bot_reply' => $hasReply ? $this->getRandomReply() : null,
                    'created_at' => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                ]);
            }
        }
    }

    private function getRandomQuestion(): string
    {
        $questions = [
            'Halo, mau tanya harga?',
            'Apakah masih buka?',
            'Bisa bantu saya?',
            'Cara order gimana?',
            'Ada promo?',
            'Bisa kirim ke Jakarta?',
            'Stok masih ada?',
            'Bisa nego?',
        ];
        return $questions[array_rand($questions)];
    }

    private function getRandomReply(): string
    {
        $replies = [
            'Halo kak! Ada yang bisa kami bantu?',
            'Kami masih buka kak, silakan order!',
            'Tentu kak, saya bantu cek dulu ya.',
            'Bisa kak, untuk order silakan hubungi kami.',
            'Saat ini belum ada promo khusus kak.',
            'Bisa kirim ke seluruh Indonesia kak.',
            'Stok masih tersedia kak.',
            'Maaf kak, harga sudah fix ya.',
        ];
        return $replies[array_rand($replies)];
    }
}
