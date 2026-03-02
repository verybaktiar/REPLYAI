@echo off
echo Running Scrape Job Scheduler...
:loop
cd /d "C:\laragon\www\REPLYAI"
php artisan scrape:process --once
timeout /t 30 /nobreak >nul
goto loop
