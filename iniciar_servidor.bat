@echo off
title Servidor Local TCS Motriz
echo ========================================================
echo   INICIANDO SERVIDOR LOCAL TCS MOTRIZ (localhost:8080)
echo ========================================================
echo.
echo Abriendo aplicacion en tu navegador...
start http://localhost:8080
echo.
echo Presiona Ctrl + C para detener el servidor.
echo.

set PHP_EXE="C:\Users\soporte\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

if exist %PHP_EXE% (
    %PHP_EXE% -S localhost:8080 -t "%~dp0app"
) else (
    php -S localhost:8080 -t "%~dp0app"
)
pause
