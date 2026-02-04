@echo off
title CEK STATUS SERVER REPLYAI
cls
echo ========================================================
echo        CEK STATUS SERVER REPLYAI
echo ========================================================
echo.

echo [1] Cek Database (MySQL Port 3306)
netstat -ano | find "3306" >nul
if %errorlevel%==0 (
    echo     STATUS: [OK] Database Aktif
) else (
    echo     STATUS: [X] Database MATI! (Pastikan Laragon Start All)
)
echo.

echo [2] Cek Web Server Laravel (Port 8000)
netstat -ano | find "8000" >nul
if %errorlevel%==0 (
    echo     STATUS: [OK] Laravel Server Aktif
) else (
    echo     STATUS: [X] Laravel Server MATI! (Jalankan: php artisan serve)
)
echo.

echo [3] Cek WhatsApp Service (Port 3001)
netstat -ano | find "3001" >nul
if %errorlevel%==0 (
    echo     STATUS: [OK] WA Service Aktif
) else (
    echo     STATUS: [X] WA Service MATI! (Jalankan: npm start di folder wa-service)
)
echo.

echo [4] Cek Cloudflare Tunnel (Akses Public)
tasklist /FI "IMAGENAME eq cloudflared.exe" | find "cloudflared.exe" >nul
if %errorlevel%==0 (
    echo     STATUS: [OK] Cloudflare Tunnel Aktif
) else (
    echo     STATUS: [X] Cloudflare Tunnel MATI! (Web tidak bisa diakses dari luar)
)
echo.

echo ========================================================
echo Tekan tombol apa saja untuk refresh...
pause >nul
call %0
