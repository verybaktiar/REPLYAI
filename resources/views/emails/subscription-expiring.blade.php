<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #f59e0b, #ef4444); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .warning-badge { display: inline-block; background: #f59e0b; color: white; padding: 8px 20px; border-radius: 50px; font-weight: bold; margin-bottom: 20px; }
        .days-left { font-size: 48px; font-weight: bold; color: #f59e0b; text-align: center; margin: 20px 0; }
        .info-box { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .cta-button { display: inline-block; background: #135bec; color: white; text-decoration: none; padding: 14px 30px; border-radius: 8px; font-weight: bold; margin-top: 20px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; color: #64748b; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Langganan Akan Berakhir</h1>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $user->name }}</strong>,</p>
            
            <span class="warning-badge">Pengingat Perpanjangan</span>
            
            <div class="days-left">{{ $daysLeft }} Hari Lagi</div>
            
            <p>Langganan <strong>{{ $plan->name ?? 'Anda' }}</strong> akan berakhir pada tanggal <strong>{{ $subscription->expires_at->format('d M Y') }}</strong>.</p>
            
            <div class="info-box">
                <p style="margin: 0;">⚠️ Setelah langganan berakhir, Anda tidak akan bisa menggunakan fitur-fitur premium seperti AI Reply, Broadcast, dan lainnya.</p>
            </div>
            
            <p>Perpanjang sekarang untuk tetap menikmati layanan tanpa gangguan!</p>
            
            <a href="{{ route('pricing') }}" class="cta-button">Perpanjang Sekarang</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
