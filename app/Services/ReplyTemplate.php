<?php

namespace App\Services;

use Illuminate\Support\Str;

class ReplyTemplate
{
    public function footer(): string
    {
        return "—\nKetik *bantuan* untuk melihat menu.";
    }

    public function appendFooter(string $text): string
    {
        $text = trim($text);

        // hindari dobel footer
        if (Str::contains(Str::lower($text), 'ketik *bantuan*')) {
            return $text;
        }

        return $text . "\n\n" . $this->footer();
    }

    public function wrap(string $title, string $body, ?string $cta = null): string
    {
        $parts = [];
        $parts[] = trim($title);

        $body = trim($body);
        if ($body !== '') $parts[] = $body;

        $cta = trim((string) $cta);
        if ($cta !== '') $parts[] = $cta;

        return $this->appendFooter(implode("\n\n", $parts));
    }

    public function welcome(): string
    {
        return $this->wrap(
            "👋 Halo, selamat datang di Layanan Informasi RS.",
            "Terima kasih sudah menghubungi kami.\nSilakan ketik salah satu kata kunci berikut:\n\n• jadwal\n• pelayanan\n• daftar poli\n• rawat inap\n• biaya\n• alamat"
        );
    }

    public function menu(): string
    {
        return $this->wrap(
            "📌 MENU BANTUAN",
            "Silakan pilih dengan mengetik salah satu kata kunci:\n1) jadwal — jadwal dokter/poli\n2) pelayanan — layanan RS\n3) daftar poli — daftar poliklinik\n4) rawat inap — info kamar & fasilitas\n5) biaya — estimasi biaya layanan\n6) alamat — lokasi & Google Maps"
        );
    }

    public function cooldown(): string
    {
        return $this->wrap(
            "⏳ Sebentar ya, kak…",
            "Kami sedang memproses pesan sebelumnya agar jawabannya akurat.",
            "Boleh ulangi pertanyaan setelah beberapa detik, atau ketik *bantuan* untuk lihat menu."
        );
    }

    public function titleFromIntent(string $text): string
    {
        $t = Str::lower($text);

        return match (true) {
            Str::contains($t, 'jadwal') => "🗓️ Jadwal Dokter",
            Str::contains($t, 'pelayanan') => "🏥 Pelayanan RS",
            Str::contains($t, 'poli') => "✅ Informasi Poliklinik",
            Str::contains($t, 'biaya') => "💳 Informasi Biaya",
            Str::contains($t, 'alamat') || Str::contains($t, 'lokasi') => "📍 Alamat & Lokasi",
            Str::contains($t, 'rawat inap') || Str::contains($t, 'kamar') => "🛏️ Rawat Inap",
            default => "🤖 Informasi"
        };
    }
}
