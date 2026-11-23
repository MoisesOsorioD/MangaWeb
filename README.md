# MangaWeb — proyecto desde cero

Aplicación web personal con **Laravel 13 + Inertia v3 + Vue 3 + TypeScript + MySQL**. Funciona como interfaz propia para el servidor **Suwayomi**, que a su vez utiliza fuentes/extensiones de Mihon. No se distribuye contenido ni el ejecutable de Suwayomi.

> **Estado de verificación:** se generó el código y se revisó sintaxis PHP y estructura. Esta copia no incluye `vendor/` ni `node_modules/`; el entorno de generación no puede descargar Composer/npm ni arrancar MySQL/Suwayomi. **No se declara la app probada de extremo a extremo.** Ejecutá INSTALAR_MANGAWEB.bat y las pruebas descritas abajo.

## Funcionalidades incluidas (código fuente)

1. Registro, inicio/cierre de sesión, actualización de perfil y contraseña; protección CSRF, validación y separación por usuario.
2. Dashboard, navegación responsive y accesos a todos los módulos.
3. Fuentes de Suwayomi, búsqueda por nombre/idioma, catálogos populares, últimos títulos, búsqueda y paginación.
4. Buscador de mangas en una fuente seleccionada o hasta cinco fuentes.
5. Detalles: portada, sinopsis, autor, artista, género, estado y capítulos.
6. Biblioteca: favoritos, estados por manga, valoraciones de 1 a 5, notas personales y colecciones.
7. Lector de imágenes: vertical e individual, ancho compacto/normal/ancho, contador flotante, botones/teclado, capítulo anterior y siguiente.
8. Progreso guardado por usuario en MySQL, historial, continuar lectura y reiniciar.
9. Marcadores de páginas, con apertura de página desde Marcadores.
10. Descargas de capítulos a almacenamiento **privado del servidor**, en cola Laravel, lectura desde caché si Suwayomi deja de estar disponible, exportación CBZ y eliminación.
11. Lista de extensiones; instalación/actualización/desinstalación de repositorios ya configurados en Suwayomi disponible **solo para el primer usuario y si se habilita** expresamente en .env.
12. Configuración del perfil, contraseña y exportación/importación JSON de biblioteca, colecciones, marcadores y progreso.
13. Comandos para Windows con Laragon, pruebas de características y script de comprobación estática.

### Aclaración sobre «offline» y «completo»

Las descargas de MangaWeb son **copias almacenadas en el servidor Laravel**, no una PWA totalmente independiente que permita abrir el sitio sin conexión de red al servidor. Para lectura en un dispositivo sin acceso al servidor, descargá el archivo `.cbz` y abrilo con un lector compatible. La compatibilidad con fuentes y extensiones depende de la versión de Suwayomi y de cada extensión. No se puede certificar sin probar en tu instalación.

## Instalación en Windows (Laragon)

Ver **GUIA_INSTALACION_WINDOWS.md**. En resumen:

- Requisitos: Windows, Laragon con **PHP 8.3 o superior**, MySQL, Composer 2, Node.js 20.19+/22.12+, npm y `ext-zip` para CBZ; Suwayomi instalado y ejecutándose por separado.
- Descomprimí `MangaWeb` en `C:\laragon\www\mangaweb-completo` (puede coexistir con el proyecto antiguo).
- En HeidiSQL o phpMyAdmin creá base de datos vacía `mangaweb_completo`.
- Abrí la terminal de Laragon en esa carpeta, ejecutá `INSTALAR_MANGAWEB.bat` (instala dependencias y compila).
- Arrancá Suwayomi en `127.0.0.1:4567`.
- Iniciá `INICIAR_MANGAWEB.bat`; abrí `http://127.0.0.1:8001/register` para crear tu usuario.
- Arrancá `INICIAR_DESCARGAS.bat` en otra ventana para procesar los capítulos descargados.
- Cuando tengas usuario, `ALLOW_REGISTRATION=false` en `.env` y `php artisan config:clear`.

## Validación y pruebas

```shell
php scripts/check_static.php
php artisan route:list
php artisan migrate:status
npm run check:vue
npm run check
php artisan test
```

Probar en el navegador: registro → instalar/extensiones en Suwayomi → explorar → portada → detalles → leer capítulo → avanzar página → historial → Biblioteca → descargar → cola → CBZ → desconectar Suwayomi y leer capítulo descargado.

`php artisan test` requiere `pdo_sqlite` para el SQLite temporal configurado en phpunit.xml.

## Seguridad

- APP_DEBUG=false y APP_ENV=production si exponés MangaWeb más allá de localhost.
- No expongás directamente el puerto 4567 ni el puerto 8001 de `artisan serve` a Internet. Usá un servidor web adecuado con HTTPS y acceso privado (por ejemplo VPN/Tailscale); documentado en la guía.
- Por defecto, `ALLOW_EXTENSION_MANAGEMENT=false`. Instalar extensiones de terceros ejecuta código en Suwayomi. Revisá su procedencia.
- Guardá `.env` fuera de repositorios y respaldá DB y `storage/app/private`.
- Las fuentes pueden servir obras protegidas por derechos de autor: usá únicamente fuentes y contenido a los que tengas derecho de acceso.

## Estructura

`app/Http/Controllers/` rutas de negocio · `app/Jobs/` descargas · `app/Services/SuwayomiService.php` integración GraphQL e imágenes · `database/migrations` tablas · `resources/js/pages` pantallas · `routes/web.php` endpoints · `storage/app/private/mangaweb` capítulos descargados.

## Dependencias y permisos

- `composer install` y `npm install` necesitan Internet la primera vez. El ZIP distribuye código propio, NO el framework Laravel, el runtime npm ni las extensiones APK/JAR.
- Usuarios y sesiones se controlan en Laravel; los permisos de Suwayomi, sus credenciales y su disponibilidad también deben estar correctamente configurados.
- El primer usuario de MangaWeb puede administrar extensiones solo si el `.env` habilita esa función; los demás no.

## Créditos

MangaWeb es una interfaz original de desarrollo personal. Laravel, Vue, Inertia, Suwayomi y las extensiones tienen licencias y mantenedores independientes. Este repositorio no copia ni distribuye los archivos de esos proyectos ni manga protegido.
