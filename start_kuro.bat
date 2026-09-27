@echo off
title Kuro Atelier - Exclusive Mini E-Commerce
color 06
echo ========================================================
echo   KURO (黒) • HAUTE PARFUMERIE 1-OF-1 BESPOKE TOKYO
echo ========================================================
echo.
echo [1/3] Memeriksa Layanan MySQL...
netstat -ano | findstr :3307 >nul
if %errorlevel% neq 0 (
    netstat -ano | findstr :3306 >nul
    if %errorlevel% neq 0 (
        echo Menjalankan MariaDB/MySQL pada port 3307...
        start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --port=3307
        timeout /t 2 /nobreak >nul
    ) else (
        echo MySQL terdeteksi aktif pada port 3306.
    )
) else (
    echo MySQL terdeteksi aktif pada port 3307.
)

echo.
echo [2/3] Memeriksa Web Server PHP...
netstat -ano | findstr :8000 >nul
if %errorlevel% neq 0 (
    echo Menjalankan PHP Web Server pada http://127.0.0.1:8000...
    start "" "C:\xampp\php\php.exe" -S 127.0.0.1:8000 -t "%~dp0"
    timeout /t 1 /nobreak >nul
) else (
    echo Web Server PHP telah aktif pada http://127.0.0.1:8000.
)
echo.
echo [3/3] Membuka Website Kuro di Browser...
start http://127.0.0.1:8000

echo.
echo ========================================================
echo   KURO ATELIER BERHASIL BERJALAN!
echo   Buka di Browser: http://127.0.0.1:8000
echo.
echo   Kredensial Bawaan:
echo   - Kuro Master (Admin): kuro@atelier.com / password123
echo   - VIP Collector (Tanaka): tanaka@executives.co.jp / password123
echo   - Kode Undangan VIP: KURO-VIP-2026 atau SHIBUI-CHAMBER
echo.
echo   *Gunakan Toolbar Persona di bagian atas website untuk
echo    berganti akun dalam 1 klik!
echo ========================================================
pause
