@echo off
chcp 65001 >nul
cls
echo ==========================================
echo  REPLYAI - Test Runner
echo ==========================================
echo.

IF "%~1"=="" GOTO MENU
IF "%1"=="unit" GOTO UNIT
IF "%1"=="feature" GOTO FEATURE
IF "%1"=="security" GOTO SECURITY
IF "%1"=="all" GOTO ALL
IF "%1"=="dusk" GOTO DUSK
IF "%1"=="coverage" GOTO COVERAGE

:MENU
echo Pilih jenis test:
echo.
echo   1. Unit Tests (Models, Services)
echo   2. Feature Tests (Controllers, Routes)
echo   3. Security Tests (Payment vulnerabilities)
echo   4. All Tests
echo   5. Browser Tests (Dusk) - Butuh Chrome
echo   6. Coverage Report
echo   0. Exit
echo.
set /p choice="Pilih (0-6): "

IF "%choice%"=="1" GOTO UNIT
IF "%choice%"=="2" GOTO FEATURE
IF "%choice%"=="3" GOTO SECURITY
IF "%choice%"=="4" GOTO ALL
IF "%choice%"=="5" GOTO DUSK
IF "%choice%"=="6" GOTO COVERAGE
IF "%choice%"=="0" GOTO END

:UNIT
echo.
echo [1/3] Running Unit Tests...
php artisan test --testsuite=Unit --colors=never
echo.
echo ✅ Unit tests completed!
GOTO PAUSE

:FEATURE
echo.
echo [1/3] Running Feature Tests...
php artisan test --testsuite=Feature --colors=never
echo.
echo ✅ Feature tests completed!
GOTO PAUSE

:SECURITY
echo.
echo [🔒] Running Security Tests...
php artisan test --filter=SecurityPaymentTest --colors=never
echo.
echo ✅ Security tests completed!
GOTO PAUSE

:ALL
echo.
echo [1/4] Running All Tests...
php artisan test --colors=never
echo.
echo ✅ All tests completed!
GOTO PAUSE

:DUSK
echo.
echo [🌐] Running Browser Tests...
echo Pastikan Chrome sudah terinstall!
echo.
php artisan dusk --colors=never
echo.
echo ✅ Browser tests completed!
GOTO PAUSE

:COVERAGE
echo.
echo [📊] Generating Coverage Report...
php artisan test --coverage --colors=never
echo.
echo ✅ Coverage report generated!
echo Buka: storage/app/coverage-report/index.html
GOTO PAUSE

:PAUSE
echo.
pause
GOTO MENU

:END
echo.
echo Goodbye!
timeout /t 2 >nul
