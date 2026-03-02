@echo off
chcp 65001 >nul
echo ========================================
echo  FIX PM2 WA-SERVICE
echo ========================================
echo.

REM Set PM2_HOME
set PM2_HOME=C:\Users\Administrator\.pm2
set PATH=%PATH%;C:\Users\Administrator\AppData\Roaming\npm

echo [1/5] Stopping dan menghapus service wa-service lama...
pm2 delete wa-service 2>nul
timeout /t 2 /nobreak >nul

echo [2/5] Clear PM2 dump...
pm2 cleardump 2>nul

echo [3/5] Starting wa-service dengan konfigurasi benar...
cd /d C:\laragon\www\REPLYAI\wa-service
pm2 start index.js --name wa-service

echo [4/5] Menyimpan konfigurasi PM2...
pm2 save

echo [5/5] Verifikasi status...
pm2 list | findstr wa-service

echo.
echo ========================================
echo  SELESAI!
echo ========================================
echo.
echo Jika service masih error, cek logs dengan:
echo   pm2 logs wa-service
echo.
pause
