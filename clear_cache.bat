@echo off
echo Limpando cache do Laravel...

C:\xampp\php\php.exe artisan cache:clear
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan route:clear
C:\xampp\php\php.exe artisan view:clear
C:\xampp\php\php.exe artisan optimize:clear

echo Cache limpo com sucesso!
pause 