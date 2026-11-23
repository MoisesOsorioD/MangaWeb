# Guía completa — MangaWeb en Laragon (Windows)

## 0. Antes de comenzar

MangaWeb es una aplicación **independiente**. No la copies encima de `C:\laragon\www\mangaweb`, para no reemplazar tu proyecto anterior. La carpeta de este ZIP se llama `MangaWeb`. Renombrala a `mangaweb-completo`.

Necesitás: Laragon con PHP >= 8.3, MySQL activo y extensiones de PHP (pdo_mysql, mbstring, openssl, fileinfo, tokenizer, xml); PHP zip si querés exportar CBZ; Composer 2; Node.js moderno y npm; Java y Suwayomi-Server ejecutándose aparte. Internet es necesario para instalar dependencias por primera vez.

## 1. Extraer

Colocá la carpeta aquí:

`C:\laragon\www\mangaweb-completo`

Dentro deben verse: `artisan`, `composer.json`, `package.json`, `routes/`, `resources/` y `app/`.

## 2. Crear base de datos

En Laragon: encendé MySQL → Menú > MySQL > HeidiSQL (o phpMyAdmin). Creá base de datos `mangaweb_completo` con `utf8mb4` (no copies datos del proyecto anterior).

## 3. Configuración inicial

Abrí una terminal desde Laragon en esa carpeta. Si aún no existe `.env`, ejecutá `copy .env.example .env`. Editá `.env` de acuerdo con tu MySQL:

```dotenv
APP_URL=http://127.0.0.1:8001
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mangaweb_completo
DB_USERNAME=root
DB_PASSWORD=
SUWAYOMI_URL=http://127.0.0.1:4567
QUEUE_CONNECTION=database
ALLOW_REGISTRATION=true
ALLOW_EXTENSION_MANAGEMENT=false
```

Si activaste seguridad propia en Suwayomi, completá `SUWAYOMI_USERNAME` y `SUWAYOMI_PASSWORD`. De ser posible dejá Suwayomi atado a localhost.

## 4. Instalar

En la terminal de Laragon y dentro de la carpeta:

```powershell
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
```

O ejecutá `INSTALAR_MANGAWEB.bat`. En caso de error del servidor MySQL, no continúes hasta corregir la conexión.

## 5. Iniciar

Primero arrancá Suwayomi Java y comprobá su interfaz en `http://127.0.0.1:4567`.

Terminal 1 (MangaWeb):

```powershell
php artisan serve --host=127.0.0.1 --port=8001
```

Terminal 2 (descargas):

```powershell
php artisan queue:work database --tries=2 --timeout=900 --sleep=2
```

También podés usar `INICIAR_MANGAWEB.bat` e `INICIAR_DESCARGAS.bat`.

Abrí `http://127.0.0.1:8001/register`, creá una cuenta, entrá y elegí Explorar.

## 6. Instalar fuentes de manga

Suwayomi no incluye automáticamente todas las fuentes. En su propio panel configurá un repositorio de extensiones de confianza. Por ejemplo, si ya tenés MangaDex Español instalado, debería aparecer en «Explorar» de MangaWeb. La administración directa de extensiones en MangaWeb requiere `ALLOW_EXTENSION_MANAGEMENT=true`, `php artisan config:clear` y la primera cuenta, y sigue dependiendo de permisos de Suwayomi.

## 7. Comprobaciones

1. Registro e inicio de sesión.
2. Explorar: lista de fuentes; entrar a MangaDex Español si está instalado.
3. Abrir un manga: portada, sinopsis y lista de capítulos.
4. Elegir capítulo: lector vertical, individual, ancho y contador.
5. Avanzar y regresar: historial, marcadores, continuar leyendo.
6. Guardar manga, editar estado, valoración, notas y colección.
7. Descargar un capítulo: revisá Descargas con la terminal de cola abierta; al quedar «Disponible» probá leerlo y exportar `.cbz`.
8. Para probar caché: cerrá Suwayomi, abrí el capítulo de Descargas; las imágenes privadas deben continuar disponibles porque se almacenaron en Laravel.
9. Exportá tus datos en Configuración; guardá el JSON y verificá que no contenga contraseñas.

## 8. Errores típicos

- «Class Controller not found»: el archivo `app/Http/Controllers/Controller.php` está incluido; ejecutá `composer dump-autoload`.
- «Vite manifest not found»: ejecutá `npm install` y `npm run build`.
- SQLSTATE 1049 Unknown database: creá `mangaweb_completo` y revisá DB_DATABASE.
- Sin fuentes: confirmá Suwayomi y su repositorio, el puerto 4567 y las credenciales.
- Descarga en cola que no cambia: iniciá `INICIAR_DESCARGAS.bat` (cola database).
- Sin exportación CBZ: habilitá PHP `extension=zip` en el php.ini de Laragon, reiniciá Laragon.
- Pantalla 419: comprobá APP_URL, cookies y CSRF; limpiar las cookies del sitio si cambiaste dominio.
- Pantalla 500: revisá `storage/logs/laravel.log` y no publiques su contenido en Internet.
- Si 8001 está ocupado, elegí otro puerto y actualizá APP_URL en .env.

## 9. Acceso remoto seguro

No uses `php artisan serve` ni `4567` directamente en Internet. Para uso remoto, instala un servidor web de producción, certificados HTTPS, sesión segura, firewall y un VPN privado como Tailscale (ver documentación oficial). En cada dispositivo, la IP del servidor debe ser accesible. Desactiva `APP_DEBUG`, cierra registros públicos y activa `SESSION_SECURE_COOKIE=true` bajo HTTPS. Ten presente que esta aplicación todavía debe probarse a nivel de despliegue, y esta guía NO equivale a un despliegue remoto automático.

## 10. Respaldos y privacidad

Respaldá: base MySQL `mangaweb_completo`, archivo `.env` protegido y `storage/app/private/mangaweb` (las páginas descargadas). El JSON exportado en Configuración NO incluye archivos descargados ni contraseñas.

## 11. Actualización y pruebas

Después de cambiar código:

```powershell
php artisan config:clear
php artisan migrate
npm run build
php artisan route:list
php artisan test
```

La suite de tests utiliza SQLite en memoria: habilitá `pdo_sqlite` para ejecutarla.
