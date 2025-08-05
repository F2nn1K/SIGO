@echo off
echo ========================================
echo    TESTE DE CONEXAO COM BANCO DE DADOS
echo ========================================
echo.

echo [1] Testando conexao direta com MySQL...
C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -e "USE laravel_beta2; SELECT 'BANCO CONECTADO: laravel_beta2' as status;"
echo.

echo [2] Verificando tabelas no banco...
C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -e "USE laravel_beta2; SHOW TABLES;"
echo.

echo [3] Contando registros na tabela permissions...
C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -e "USE laravel_beta2; SELECT COUNT(*) as total_permissions FROM permissions;"
echo.

echo [4] Contando registros na tabela profiles...
C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -e "USE laravel_beta2; SELECT COUNT(*) as total_profiles FROM profiles;"
echo.

echo [5] Mostrando primeiras 3 permissoes...
C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -e "USE laravel_beta2; SELECT id, name, code, description FROM permissions LIMIT 3;"
echo.

echo [6] Testando conexao Laravel...
C:\xampp\php\php.exe artisan tinker --execute="echo 'Laravel conectado em: ' . DB::connection()->getDatabaseName() . PHP_EOL; echo 'Permissoes encontradas: ' . DB::table('permissions')->count() . PHP_EOL; echo 'Perfis encontrados: ' . DB::table('profiles')->count() . PHP_EOL;"
echo.

echo [7] Verificando arquivo .env...
if exist .env (
    echo "Arquivo .env EXISTE"
    findstr "DB_" .env
) else (
    echo "Arquivo .env NAO EXISTE!"
    echo "Usando configuracoes do env_config.txt:"
    findstr "DB_" env_config.txt
)
echo.

echo ========================================
echo           TESTE FINALIZADO
echo ========================================
pause 