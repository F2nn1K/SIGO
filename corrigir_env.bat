@echo off
echo ========================================
echo     CORRIGINDO ARQUIVO .ENV
echo ========================================
echo.

echo [1] Fazendo backup do .env atual...
if exist .env (
    copy .env .env.backup
    echo Backup criado: .env.backup
) else (
    echo Arquivo .env nao existe, criando novo...
)
echo.

echo [2] Criando novo .env com configuracoes MySQL...
(
echo APP_NAME="Sistema BRS"
echo APP_ENV=local
echo APP_KEY=base64:VnBJOUVYdnRsQW9KZnJMWjZpTTNkdnVJNWdRMmFNSVE=
echo APP_DEBUG=true
echo APP_URL=http://localhost
echo.
echo LOG_CHANNEL=stack
echo LOG_DEPRECATIONS_CHANNEL=null
echo LOG_LEVEL=debug
echo.
echo DB_CONNECTION=mysql
echo DB_HOST=127.0.0.1
echo DB_PORT=3306
echo DB_DATABASE=laravel_beta2
echo DB_USERNAME=root
echo DB_PASSWORD=
echo.
echo BROADCAST_DRIVER=log
echo CACHE_DRIVER=file
echo FILESYSTEM_DISK=local
echo QUEUE_CONNECTION=sync
echo SESSION_DRIVER=file
echo SESSION_LIFETIME=120
echo.
echo MEMCACHED_HOST=127.0.0.1
echo.
echo REDIS_HOST=127.0.0.1
echo REDIS_PASSWORD=null
echo REDIS_PORT=6379
echo.
echo MAIL_MAILER=smtp
echo MAIL_HOST=mailpit
echo MAIL_PORT=1025
echo MAIL_USERNAME=null
echo MAIL_PASSWORD=null
echo MAIL_ENCRYPTION=null
echo MAIL_FROM_ADDRESS="hello@example.com"
echo MAIL_FROM_NAME="${APP_NAME}"
echo.
echo AWS_ACCESS_KEY_ID=
echo AWS_SECRET_ACCESS_KEY=
echo AWS_DEFAULT_REGION=us-east-1
echo AWS_BUCKET=
echo AWS_USE_PATH_STYLE_ENDPOINT=false
echo.
echo PUSHER_APP_ID=
echo PUSHER_APP_KEY=
echo PUSHER_APP_SECRET=
echo PUSHER_HOST=
echo PUSHER_PORT=443
echo PUSHER_SCHEME=https
echo PUSHER_APP_CLUSTER=mt1
echo.
echo VITE_APP_NAME="${APP_NAME}"
echo VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
echo VITE_PUSHER_HOST="${PUSHER_HOST}"
echo VITE_PUSHER_PORT="${PUSHER_PORT}"
echo VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
echo VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
) > .env

echo Arquivo .env criado com configuracoes MySQL!
echo.

echo [3] Limpando cache do Laravel...
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear
echo Cache limpo!
echo.

echo [4] Testando nova conexao...
C:\xampp\php\php.exe artisan tinker --execute="echo 'Laravel agora conectado em: ' . DB::connection()->getDatabaseName() . PHP_EOL; echo 'Permissoes encontradas: ' . DB::table('permissions')->count() . PHP_EOL; echo 'Perfis encontrados: ' . DB::table('profiles')->count() . PHP_EOL;"
echo.

echo ========================================
echo        CORRECAO FINALIZADA!
echo ========================================
echo Agora o Laravel deve estar conectado no MySQL!
pause 