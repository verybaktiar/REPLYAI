<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KbArticle;
use App\Models\BusinessProfile;
use App\Models\User;

class KbExpansionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get User (Assume ID 1)
        $user = User::first();
        
        if (!$user) {
            $user = User::factory()->create();
        }

        // 2. Get or Create Business Profile for "Coffee Shop Arasta"
        // We'll try to find one, or create a new one for Arasta
        $profile = BusinessProfile::firstOrCreate(
            ['business_name' => 'Coffee Shop Arasta'],
            [
                'user_id' => $user->id,
                'business_type' => 'fnb',
                'is_active' => true,
            ]
        );

        // 3. Define the Articles
        $articles = [
            [
                'title' => 'Lokasi & Akses',
                'content' => "Kami di Jl. Mawar No. 123, dekat SPBU Pertamina dan Indomaret. \nAda parkir motor di depan dan parkir mobil di seberang. \nDari halte TransJakarta berjalan 5 menit. \nSearch 'Coffee Shop Arasta' di Google Maps ya kak 📍",
                'tags' => 'lokasi, alamat, peta, maps, parkir, akses, transportasi',
            ],
            [
                'title' => 'Jam Operasional',
                'content' => "Buka setiap hari Senin-Minggu jam 08.00-22.00 WIB. \nLast order jam 21.30 ya kak. \nJam ramai biasanya 12.00-14.00 dan 19.00-21.00. \nKalau mau quiet time, recommended datang jam 15.00-17.00 ☕",
                'tags' => 'jam buka, operasional, jadwal, tutup, last order, waktu',
            ],
            [
                'title' => 'Fasilitas & Layanan',
                'content' => "Ada WiFi gratis 'ArastaCoffee' password 'ngopi2024', \nstop kontak di setiap meja buat nge-charge laptop, \nfull AC, toilet bersih, dan ada area outdoor smoking zone. \nTempatnya cozy buat kerja atau meeting kecil kak 💻",
                'tags' => 'fasilitas, wifi, internet, toilet, smoking, ac, colokan, listrik',
            ],
            [
                'title' => 'Reservasi & Booking',
                'content' => "Untuk booking meja bisa WA ke nomor ini kak, \nminimal 2 hari sebelumnya untuk grup 6+ orang. \nTidak perlu DP, tapi tolong konfirmasi 1 jam sebelum datang. \nUntuk private event (birthday/meeting) bisa juga, \nkapasitas maksimal 20 orang. Tanya-tanya dulu boleh kok 😊",
                'tags' => 'reservasi, booking, meja, tempat, event, ulang tahun, meeting',
            ],
            [
                'title' => 'Metode Pembayaran',
                'content' => "Bisa tunai, transfer ke BCA/Mandiri, atau QRIS (GoPay, OVO, DANA, LinkAja). \nKartu debit/kredit juga bisa kak. \nUntuk split bill maksimal 2 struk ya kak 🙏",
                'tags' => 'pembayaran, payment, transfer, qris, tunai, kartu, debit, kredit, split bill',
            ],
            [
                'title' => 'Menu Spesial & Customisasi',
                'content' => "Gula bisa 0-100% gratis ya kak. \nMau ganti oat milk tambah Rp10.000. \nExtra shot espresso tambah Rp5.000. \nUkuran cuma regular satu ukuran. \nKadang ada menu seasonal, info terbaru cek IG @arastacoffee 🍵",
                'tags' => 'menu, custom, gula, sugar, susu, milk, oat milk, espresso, ukuran, size',
            ],
            [
                'title' => 'Delivery & Order Online',
                'content' => "Minimal order Rp50.000 untuk delivery radius 5km. \nBisa via GoFood, GrabFood, atau WA ini untuk delivery mandiri \n(ongkir menyesuaikan jarak). \nEstimasi 20-40 menit tergantung lokasi kak 🛵",
                'tags' => 'delivery, pesan antar, online, gofood, grabfood, ongkir',
            ],
            [
                'title' => 'Program Loyalty',
                'content' => "Ada digital member kak! Daftar gratis, tiap Rp10.000 = 1 poin. \nKumpulin 50 poin dapat 1 minuman gratis. \nBirthday month dapat diskon 20% juga 🎉",
                'tags' => 'loyalty, member, poin, reward, hadiah, gratis',
            ],
        ];

        // 4. Insert Articles
        foreach ($articles as $data) {
            KbArticle::updateOrCreate(
                [
                    'business_profile_id' => $profile->id,
                    'title' => $data['title']
                ],
                [
                    'user_id' => $user->id,
                    'content' => $data['content'],
                    'tags' => $data['tags'],
                    'is_active' => true,
                    'source_url' => 'system_seeder',
                ]
            );
        }
        
        $this->command->info('Knowledge Base expanded with operational info for Coffee Shop Arasta!');
    }
}
