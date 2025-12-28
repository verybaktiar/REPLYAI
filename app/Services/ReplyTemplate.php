<?php

namespace App\Services;

use Illuminate\Support\Str;

class ReplyTemplate
{
    public function footer(): string
    {
        return "\n───────────────\n💡 Ketik *menu* atau *bantuan* kapan saja.";
    }

    public function appendFooter(string $text): string
    {
        $text = trim($text);

        // hindari dobel footer
        if (Str::contains(Str::lower($text), 'ketik *menu*')) {
            return $text;
        }

        return $text . "\n" . $this->footer();
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
            "👋 Halo, Selamat Datang!",
            "Saya asisten virtual RS PKU Muhammadiyah Surakarta siap membantu kakak.",
            "Berikut hal yang bisa saya bantu:\n\n" .
            "🗓️ *Jadwal* — Cek jadwal dokter/poli\n" .
            "🏥 *Pelayanan* — Info layanan & fasilitas\n" .
            "👨‍⚕️ *Daftar Poli* — List poliklinik tersedia\n" .
            "🛏️ *Rawat Inap* — Ketersediaan kamar\n" .
            "💳 *Biaya* — Estimasi biaya layanan\n" .
            "📍 *Lokasi* — Alamat & Google Maps"
        );
    }

    public function menu(): string
    {
        return $this->wrap(
            "📱 MENU UTAMA",
            "Silakan ketik salah satu kata kunci di bawah ini:",
            "🗓️ *Jadwal* \n   ↳ Cek praktek dokter & jam buka\n\n" .
            "🏥 *Pelayanan* \n   ↳ Info layanan medis & penunjang\n\n" .
            "👨‍⚕️ *Daftar Poli* \n   ↳ Lihat semua spesialis kami\n\n" .
            "🛏️ *Rawat Inap* \n   ↳ Info kamar & fasilitas\n\n" .
            "💳 *Biaya* \n   ↳ Info administrasi & tarif\n\n" .
            "📍 *Lokasi* \n   ↳ Peta lokasi rumah sakit"
        );
    }

    public function cooldown(): string
    {
        return $this->wrap(
            "⏳ Mohon Tunggu Sebentar",
            "Saya sedang mengetik jawaban untuk kakak...",
            "Jika belum muncul, silakan ketik ulang pertanyaan kakak dalam beberapa detik ya."
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
