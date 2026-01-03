@echo off
chcp 65001 >nul
echo ========================================
echo 🚀 Starting ReplyAI Bot...
echo ========================================
echo.

echo [1/3] Starting WhatsApp Service (Node.js)...
start "WhatsApp Service" cmd /k "cd C:\laragon\www\Projek\Replyai\REPLYAI\wa-service && npm start"

timeout /t 2 >nul

echo [2/3] Starting Cloudflare Tunnel...
start "Cloudflare Tunnel" cmd /k "cd C:\Users\Admin\bin && .\cloudflared tunnel run replai-bot"

timeout /t 3 >nul

echo [3/3] Starting Laravel Server...
start "Laravel Server" cmd /k "cd C:\laragon\www\Projek\Replyai\REPLYAI && php artisan serve"

echo.
echo ========================================
echo ✅ Bot dimulai! Semua terminal harus tetap terbuka
echo ========================================
echo.
echo 🌐 Akses di: http://127.0.0.1:8000
echo 📱 Webhook: https://bot.mialmuhajirin.sch.id/api/instagram/webhook
echo 💬 WA Service: http://127.0.0.1:3001/health
echo.
timeout /t 5 >nul
