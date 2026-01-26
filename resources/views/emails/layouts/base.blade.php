<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'ReplyAI' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            margin: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #135bec 0%, #8b5cf6 100%);
            padding: 30px;
            text-align: center;
        }
        .logo {
            font-size: 28px;
            font-weight: 800;
            color: white;
        }
        .logo-reply { color: white; }
        .logo-ai { color: #fbbf24; }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 20px;
        }
        .content {
            font-size: 16px;
            color: #4a4a68;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #135bec 0%, #8b5cf6 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .info-box {
            background-color: #f0f4ff;
            border-left: 4px solid #135bec;
            padding: 15px 20px;
            border-radius: 0 8px 8px 0;
            margin: 25px 0;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer-text {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 15px;
        }
        .social-links {
            margin-bottom: 15px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #135bec;
            text-decoration: none;
        }
        .unsubscribe {
            font-size: 12px;
            color: #94a3b8;
        }
        .unsubscribe a {
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo">
                <span class="logo-reply">REPLY</span><span class="logo-ai">AI</span>
            </div>
        </div>

        <!-- Body -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-text">
                © {{ date('Y') }} ReplyAI. All rights reserved.
            </div>
            <div class="social-links">
                <a href="#">Website</a> |
                <a href="#">Instagram</a> |
                <a href="#">WhatsApp</a>
            </div>
            <div class="unsubscribe">
                <a href="#">Kelola preferensi email</a>
            </div>
        </div>
    </div>
</body>
</html>
