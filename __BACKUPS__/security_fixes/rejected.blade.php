@component('mail::message')
# Pembayaran Tidak Valid

Halo {{ $payment->user->name }},

Mohon maaf, pembayaran dengan invoice **{{ $payment->invoice_number }}** tidak dapat kami verifikasi.

---

**Detail Transaksi:**
- **Paket:** {{ $plan->name }}
- **Total:** Rp {{ number_format($payment->total, 0, ',', '.') }}

**Alasan Penolakan:**
{{ $reason }}

---

Silakan upload ulang bukti transfer yang valid atau hubungi admin untuk bantuan lebih lanjut.

@component('mail::button', ['url' => route('checkout.payment', $payment->invoice_number)])
Coba Lagi
@endcomponent

Jika ada pertanyaan, silakan hubungi tim support kami.

Salam,<br>
{{ config('app.name') }}
@endcomponent
