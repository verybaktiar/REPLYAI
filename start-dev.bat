@echo off
echo Memulai REPLYAI Development Environment...
echo.

echo 1. Menjalankan Laravel Server (Port 8000)...
start "Laravel Server" cmd /k "php artisan serve"

echo 2. Menjalankan Queue Worker (untuk kirim pesan)...
start "Queue Worker" cmd /k "php artisan queue:work"

echo 3. Menjalankan Reverb Server (untuk real-time)...
start "Reverb Server" cmd /k "php artisan reverb:start"

echo 4. Menjalankan Frontend Build (Vite)...
start "Frontend Build" cmd /k "npm run dev"

echo.
echo Semua service telah dijalankan di jendela terpisah.
echo Jangan tutup jendela-jendela tersebut agar sistem berjalan normal.
pause
