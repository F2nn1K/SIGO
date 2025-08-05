@echo off
echo ========================================
echo     IMPORTANDO BANCO DE DADOS
echo ========================================
echo.

REM Verificar se o arquivo SQL existe
if not exist "C:\Users\TI\Documents\sistema interno\laravel_beta2.sql" (
    echo ERRO: Arquivo laravel_beta2.sql nao encontrado!
    echo Verifique se o arquivo esta em: C:\Users\TI\Documents\sistema interno\
    pause
    exit /b 1
)

echo 1. Criando banco de dados laravel_beta2...
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS laravel_beta2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if errorlevel 1 (
    echo ERRO: Falha ao criar o banco de dados!
    echo Verifique se o MySQL esta rodando no XAMPP
    pause
    exit /b 1
)

echo 2. Importando estrutura e dados do arquivo SQL...
C:\xampp\mysql\bin\mysql.exe -u root laravel_beta2 < "C:\Users\TI\Documents\sistema interno\laravel_beta2.sql"

if errorlevel 1 (
    echo ERRO: Falha ao importar o arquivo SQL!
    pause
    exit /b 1
)

echo.
echo ========================================
echo   BANCO IMPORTADO COM SUCESSO!
echo ========================================
echo.
echo Banco de dados: laravel_beta2
echo Servidor: localhost
echo Usuario: root
echo Senha: (vazio)
echo.
echo Agora voce pode:
echo 1. Acessar phpMyAdmin: http://localhost/phpmyadmin
echo 2. Configurar o arquivo .env do projeto
echo 3. Iniciar o servidor Laravel
echo.
pause