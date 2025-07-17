@echo off
echo ===== Iniciando servidor Laravel =====
echo.
echo IMPORTANTE: Certifique-se que o XAMPP Control Panel está aberto
echo e que os serviços Apache e MySQL estão rodando (botões verdes)
echo.
echo O servidor será iniciado na porta 8000.
echo Acesse http://localhost:8000 no seu navegador.
echo.
echo Pressione CTRL+C para encerrar o servidor quando terminar.
echo.
C:\xampp\php\php.exe -S localhost:8000 -t public
pause 