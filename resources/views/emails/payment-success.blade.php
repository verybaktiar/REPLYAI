@extends('emails.layouts.base')

@section('content')
<h1 class="greeting">Pembayaran Berhasil! ✅</h1>

<p class="content">
    Hai <strong>{{ $user->name }}</strong>,<br><br>
    Terima kasih! Pembayaran kamu telah berhasil diproses.
</p>

<div style="background: #f0fdf4; border: 1px solid #22c55e; border-radius: 8px; padding: 20px; margin: 25px 0;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #64748b;">Paket</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $plan->name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #64748b;">Periode</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $period }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #64748b;">Total Pembayaran</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #22c55e;">Rp {{ number_format($amount) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #64748b;">Invoice ID</td>
            <td style="padding: 8px 0; text-align: right; font-family: monospace;">{{ $invoiceId }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #64748b;">Tanggal</td>
            <td style="padding: 8px 0; text-align: right;">{{ $date }}</td>
        </tr>
    </table>
</div>

<p class="content">
    Langganan kamu aktif hingga <strong>{{ $expiresAt }}</strong>. Kamu akan mendapat reminder sebelum masa aktif berakhir.
</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/dashboard') }}" class="btn">Buka Dashboard</a>
</p>

<p class="content" style="font-size: 14px; color: #64748b;">
    Invoice lengkap bisa diunduh dari halaman Billing di dashboard.
</p>

<p class="content">
    Salam,<br>
    <strong>Tim ReplyAI</strong>
</p>
@endsection
