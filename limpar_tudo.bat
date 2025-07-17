@echo off
echo ===================================================
echo  LIMPEZA COMPLETA E VERIFICACAO DO SISTEMA
echo ===================================================
echo.

cd %~dp0

echo Limpando TODOS os caches do Laravel...
php artisan optimize:clear
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
echo.

echo Verificando o banco de dados...
php -r "
    require __DIR__ . '/vendor/autoload.php';
    \$app = require_once __DIR__ . '/bootstrap/app.php';
    \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    
    echo \"Tabelas encontradas:\\n\";
    \$tables = ['profiles', 'permissions', 'profile_permissions'];
    foreach (\$tables as \$table) {
        echo \"- {\$table}: \" . (Schema::hasTable(\$table) ? 'OK' : 'NAO ENCONTRADA') . \"\\n\";
    }
    
    echo \"\\nRegistros nas tabelas:\\n\";
    echo \"- profiles: \" . DB::table('profiles')->count() . \" registros\\n\";
    echo \"- permissions: \" . DB::table('permissions')->count() . \" registros\\n\";
    echo \"- profile_permissions: \" . DB::table('profile_permissions')->count() . \" registros\\n\";
    
    echo \"\\nVerificando rotas:\\n\";
    \$routes = Route::getRoutes();
    \$found = false;
    foreach (\$routes as \$route) {
        if (strpos(\$route->uri, 'perfis') !== false || strpos(\$route->uri, 'permissoes') !== false) {
            echo \"- \" . implode('|', \$route->methods) . \" \" . \$route->uri . \"\\n\";
            \$found = true;
        }
    }
    if (!\$found) {
        echo \"Nenhuma rota encontrada para perfis ou permissões!\\n\";
    }
"
echo.

echo ===================================================
echo  VERIFICACAO CONCLUIDA!
echo ===================================================
echo.
echo Agora tente acessar novamente as paginas:
echo.
echo - Perfis: http://localhost:8000/perfis
echo - Permissoes: http://localhost:8000/permissoes
echo.

pause 