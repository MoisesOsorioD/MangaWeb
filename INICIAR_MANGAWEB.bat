@echo off
cd /d "%~dp0"
echo Abri http://127.0.0.1:8001/register la primera vez.
echo Suwayomi debe ejecutarse de forma independiente en el puerto 4567.
php artisan serve --host=127.0.0.1 --port=8001
pause
