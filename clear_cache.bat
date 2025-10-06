@echo off
setlocal ENABLEDELAYEDEXPANSION

REM Ir para a pasta do projeto
cd /d "%~dp0"

echo Limpando cache do Laravel...

REM Detectar php.exe
set "PHP_EXE="
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if not defined PHP_EXE for /f "delims=" %%I in ('where php 2^>NUL') do (
    if not defined PHP_EXE set "PHP_EXE=%%~fI"
)

if not defined PHP_EXE (
    echo [ERRO] Nao foi possivel localizar o php.exe. Instale o XAMPP ou adicione o PHP ao PATH.
    pause
    exit /b 1
)

"%PHP_EXE%" artisan cache:clear
"%PHP_EXE%" artisan config:clear
"%PHP_EXE%" artisan route:clear
"%PHP_EXE%" artisan view:clear
"%PHP_EXE%" artisan optimize:clear

echo Cache limpo com sucesso!
pause
endlocal