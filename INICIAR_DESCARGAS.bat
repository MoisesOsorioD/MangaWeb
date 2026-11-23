@echo off
cd /d "%~dp0"
echo Procesando descargas de capitulos. Dejar esta ventana abierta.
php artisan queue:work database --tries=2 --timeout=900 --sleep=2
pause
