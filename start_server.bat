@echo off
setlocal ENABLEDELAYEDEXPANSION

REM Navegar para a pasta do projeto (beta2)
cd /d "%~dp0"

echo ===== Iniciando servidor Laravel (ambiente local) =====
echo.
echo 1) Garanta que o XAMPP esteja com Apache e MySQL iniciados.
echo 2) O servidor subira em http://localhost:8000
echo.

REM Detectar php.exe do XAMPP (fallback para PHP no PATH)
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

REM Criar .env local automaticamente se nao existir
if not exist .env (
    echo Criando arquivo .env local com configuracoes padrao do XAMPP...
    copy /Y NUL .env >NUL 2>&1
    (
        echo APP_NAME="SIGO"
        echo APP_ENV=local
        echo APP_KEY=base64:VnBJOUVYdnRsQW9KZnJMWjZpTTNkdnVJNWdRMmFNSVE=
        echo APP_DEBUG=true
        echo APP_URL=http://localhost:8000
        echo
        echo LOG_CHANNEL=stack
        echo LOG_LEVEL=debug
        echo
        echo DB_CONNECTION=mysql
        echo DB_HOST=127.0.0.1
        echo DB_PORT=3306
        echo DB_DATABASE=u816756411_laravel_beta2
        echo DB_USERNAME=root
        echo DB_PASSWORD=
        echo
        echo BROADCAST_DRIVER=log
        echo CACHE_DRIVER=file
        echo FILESYSTEM_DISK=local
        echo QUEUE_CONNECTION=sync
        echo SESSION_DRIVER=file
        echo SESSION_LIFETIME=120
    )>>.env
)

REM Limpar caches para evitar configs antigas
"%PHP_EXE%" artisan optimize:clear >NUL 2>&1

REM Abrir navegador automaticamente
start "" http://localhost:8000

REM Se existir public\index.php (padrão Laravel), usar artisan serve; caso contrario, usar servidor embutido com public_html
if exist public\index.php (
    echo Detectado public\index.php. Iniciando com ^`artisan serve^`...
    "%PHP_EXE%" artisan serve --host=127.0.0.1 --port=8000
) else (
    echo Pasta public\ nao encontrada. Servindo via public_html\ com roteador interno...
    if not exist "..\public_html\index.php" (
        echo [ERRO] public_html\index.php nao encontrado. Verifique a estrutura do projeto.
        pause
        exit /b 1
    )
    pushd "..\public_html"
    echo Iniciando servidor PHP embutido com roteador public_html\router.php ...
    "%PHP_EXE%" -S 127.0.0.1:8000 router.php
    popd
)

endlocal