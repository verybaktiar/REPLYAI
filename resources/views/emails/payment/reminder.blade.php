@component('mail::message')
# ⏰ Pengingat Pembayaran

Halo {{ $user->name }},

Ini adalah pengingat bahwa invoice Anda akan segera berakhir.

---

**Invoice:** {{ $payment->invoice_number }}  
**Paket:** {{ $plan->name }}  
**Total:** Rp {{ number_format($payment->total, 0, ',', '.') }}  
**Berakhir:** {{ $timeLeft }}

---

⚠️ **Jika Anda tidak menyelesaikan pembayaran sebelum waktu habis, invoice ini akan dibatalkan secara otomatis.**

@component('mail::button', ['url' => route('checkout.payment', $payment->invoice_number)])
Bayar Sekarang
@endcomponent

Jika Anda sudah membayar, abaikan email ini.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
