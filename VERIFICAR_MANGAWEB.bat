@echo off
cd /d "%~dp0"
php scripts/check_static.php
php artisan route:list
php artisan migrate:status
call npm run check:vue
call npm run check
pause
