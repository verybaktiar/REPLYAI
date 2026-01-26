@extends('emails.layouts.base')

@section('content')
<h1 class="greeting">Selamat Datang di ReplyAI! 🎉</h1>

<p class="content">
    Hai <strong>{{ $user->name }}</strong>,<br><br>
    Terima kasih sudah mendaftar di ReplyAI! Kami senang kamu bergabung dengan ribuan bisnis yang sudah meningkatkan customer service mereka dengan AI.
</p>

<div class="info-box">
    <strong>🚀 Langkah Selanjutnya:</strong>
    <ol style="margin: 10px 0 0; padding-left: 20px;">
        <li>Hubungkan akun WhatsApp atau Instagram</li>
        <li>Setup Knowledge Base dengan info bisnis kamu</li>
        <li>Aktifkan auto-reply dan lihat hasilnya!</li>
    </ol>
</div>

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/dashboard') }}" class="btn">Masuk ke Dashboard</a>
</p>

<p class="content">
    Butuh bantuan? Tim support kami siap membantu 24/7 melalui chat di dalam aplikasi.
</p>

<p class="content">
    Salam hangat,<br>
    <strong>Tim ReplyAI</strong>
</p>
@endsection
