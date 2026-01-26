<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - REPLYAI</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0a0e17;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background: linear-gradient(180deg, #141b2a 0%, #1c2537 100%); border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.4);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 30px; text-align: center; background: linear-gradient(135deg, #135bec 0%, #0d47a1 100%);">
                            <table role="presentation" style="margin: 0 auto;">
                                <tr>
                                    <td>
                                        <span style="font-size: 32px; font-weight: 900; color: #ffffff; letter-spacing: -1px;">REPLY</span>
                                        <span style="font-size: 32px; font-weight: 900; color: #ffffff; letter-spacing: -1px; opacity: 0.9;">AI</span>
                                    </td>
                                </tr>
                            </table>
                            <p style="color: rgba(255,255,255,0.8); font-size: 14px; margin: 15px 0 0;">
                                Automate Your Conversations with AI
                            </p>
                        </td>
                    </tr>

                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <!-- Welcome Icon -->
                            <div style="text-align: center; margin-bottom: 30px;">
                                <div style="width: 80px; height: 80px; margin: 0 auto; background: rgba(19, 91, 236, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 40px;">✉️</span>
                                </div>
                            </div>

                            <h1 style="color: #ffffff; font-size: 24px; font-weight: 700; text-align: center; margin: 0 0 10px;">
                                Verifikasi Email Anda
                            </h1>
                            
                            <p style="color: #94a3b8; font-size: 16px; line-height: 1.6; text-align: center; margin: 0 0 30px;">
                                Halo <strong style="color: #ffffff;">{{ $user->name }}</strong>! 👋<br>
                                Terima kasih sudah mendaftar di REPLYAI. Silakan klik tombol di bawah untuk memverifikasi alamat email Anda.
                            </p>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $verificationUrl }}" 
                                   style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #135bec 0%, #1e88e5 100%); color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 12px; box-shadow: 0 4px 15px rgba(19, 91, 236, 0.4);">
                                    ✓ Verifikasi Email Saya
                                </a>
                            </div>

                            <!-- Security Note -->
                            <div style="background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3); border-radius: 12px; padding: 16px; margin: 30px 0;">
                                <p style="color: #fbbf24; font-size: 13px; margin: 0; line-height: 1.5;">
                                    ⏰ <strong>Link ini berlaku selama 60 menit.</strong><br>
                                    Jika Anda tidak mendaftar di REPLYAI, abaikan email ini.
                                </p>
                            </div>

                            <!-- Fallback Link -->
                            <p style="color: #64748b; font-size: 13px; text-align: center; margin: 20px 0 0; word-break: break-all;">
                                Jika tombol tidak berfungsi, copy link ini:<br>
                                <a href="{{ $verificationUrl }}" style="color: #60a5fa; text-decoration: none;">{{ $verificationUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1);">
                            <table role="presentation" style="width: 100%;">
                                <tr>
                                    <td style="text-align: center;">
                                        <p style="color: #64748b; font-size: 13px; margin: 0 0 10px;">
                                            Butuh bantuan? <a href="mailto:support@replyai.id" style="color: #60a5fa; text-decoration: none;">support@replyai.id</a>
                                        </p>
                                        <p style="color: #475569; font-size: 12px; margin: 0;">
                                            © {{ date('Y') }} REPLYAI. All rights reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

                <!-- Bottom Note -->
                <p style="color: #475569; font-size: 11px; text-align: center; margin: 20px 0 0;">
                    Email ini dikirim otomatis. Mohon jangan membalas email ini.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
