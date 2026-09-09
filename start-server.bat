@echo off
title Barangay Management System (BarangayOS) - PHP & MySQL Edition
cd /d "%~dp0"

echo ============================================================
echo   BARANGAY MANAGEMENT SYSTEM (BarangayOS)
echo   PHP 8+ & MySQL Stack Edition
echo ============================================================
echo.

set PHP_BIN=php
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set PHP_BIN=C:\xampp\php\php.exe
    ) else if exist "C:\laragon\bin\php\current\php.exe" (
        set PHP_BIN=C:\laragon\bin\php\current\php.exe
    ) else (
        echo [!] PHP was not found in system PATH or standard XAMPP location.
        echo.
        echo To run with XAMPP:
        echo   1. Start Apache and MySQL in the XAMPP Control Panel.
        echo   2. Ensure this folder is inside your XAMPP htdocs (e.g. C:\xampp\htdocs\Barangay).
        echo   3. Open your browser to: http://localhost/Barangay/install.php
        echo.
        echo Attempting to launch http://localhost/Barangay/install.php ...
        start "" "http://localhost/Barangay/install.php"
        pause
        exit /b 0
    )
)

echo [OK] Using PHP executable: %PHP_BIN%
echo [*] Starting local PHP server on http://localhost:8000 ...
echo [*] Launching Setup & Installer wizard in your default browser...
echo.
echo Press Ctrl+C to stop the server at any time.
echo ============================================================
echo.

start "" "http://localhost:8000/install.php"
"%PHP_BIN%" -S localhost:8000
