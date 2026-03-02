@component('mail::message')
# Pembayaran Berhasil! 🎉

Halo {{ $user->name }},

Terima kasih atas pembayaran Anda. Berikut adalah detail transaksi:

---

**Invoice:** {{ $payment->invoice_number }}  
**Paket:** {{ $plan->name }}  
**Durasi:** {{ $payment->duration_months }} bulan  
**Total Pembayaran:** Rp {{ number_format($payment->total, 0, ',', '.') }}

**Status:** ✅ **PAID**

---

Langganan Anda sekarang sudah aktif. Anda dapat mengakses semua fitur premium sekarang juga.

@component('mail::button', ['url' => route('dashboard')])
Masuk ke Dashboard
@endcomponent

Jika ada pertanyaan, silakan hubungi tim support kami.

Salam,<br>
{{ config('app.name') }}
@endcomponent
