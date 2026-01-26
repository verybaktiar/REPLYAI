@extends('emails.layouts.base')

@section('content')
<h1 class="greeting">Reset Password 🔐</h1>

<p class="content">
    Hai <strong>{{ $user->name }}</strong>,<br><br>
    Kami menerima permintaan untuk mereset password akun ReplyAI kamu. Klik tombol di bawah untuk membuat password baru.
</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
</p>

<div class="info-box">
    <strong>⚠️ Penting:</strong><br>
    Link ini hanya berlaku selama <strong>60 menit</strong>. Jika kamu tidak meminta reset password, abaikan email ini.
</p>
</div>

<p class="content" style="font-size: 14px; color: #64748b;">
    Jika tombol tidak berfungsi, copy dan paste link berikut ke browser:<br>
    <a href="{{ $resetUrl }}" style="color: #135bec; word-break: break-all;">{{ $resetUrl }}</a>
</p>

<p class="content">
    Salam,<br>
    <strong>Tim ReplyAI</strong>
</p>
@endsection
