@echo off
echo Limpando o cache do Laravel...
php artisan optimize:clear
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
echo Cache limpo com sucesso!
pause 