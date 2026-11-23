@echo off
setlocal
cd /d "%~dp0"
echo ================================================
echo  MANGAWEB - INSTALACION EN WINDOWS CON LARAGON
echo ================================================
where php >nul 2>&1 || (echo ERROR: PHP no esta en el PATH. Abrir Terminal de Laragon.& pause & exit /b 1)
where composer >nul 2>&1 || (echo ERROR: Composer no esta en el PATH.& pause & exit /b 1)
where npm >nul 2>&1 || (echo ERROR: Node/npm no esta instalado.& pause & exit /b 1)
if not exist .env (copy .env.example .env >nul)
echo.
echo AVISO: Antes de continuar, crear la base de datos mangaweb_completo en Laragon/HeidiSQL.
echo Revisar DB_USERNAME y DB_PASSWORD del archivo .env.
pause
call composer install || (echo FALLO composer install & pause & exit /b 1)
php artisan key:generate || (echo FALLO key:generate & pause & exit /b 1)
php artisan migrate || (echo FALLO migraciones. Revisar MySQL. & pause & exit /b 1)
call npm install || (echo FALLO npm install & pause & exit /b 1)
call npm run build || (echo FALLO Vue/Vite. & pause & exit /b 1)
echo.
echo INSTALACION FINALIZADA. Ejecutar INICIAR_MANGAWEB.bat
pause
