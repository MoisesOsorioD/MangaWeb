# Referencias técnicas

- Laravel 13: https://laravel.com/docs/13.x
- Inertia Laravel y Vue: https://inertiajs.com
- Suwayomi Server: https://github.com/Suwayomi/Suwayomi-Server
- Esquema GraphQL de Suwayomi VUI: https://github.com/Suwayomi/Suwayomi-VUI/blob/main/schema.graphql

La API de Suwayomi puede cambiar con la versión. Se utilizaron los campos confirmados anteriormente en este proyecto (sources, fetchSourceManga, fetchManga, fetchChapters, fetchChapterPages). La actualización de extensiones usa `updateExtension(input:{id,patch})` (API reciente). Esta compatibilidad deberá verificarse contra tu versión de Suwayomi.

Las imágenes no se distribuyen en el ZIP; las sirven las fuentes elegidas por el usuario. No se distribuyen extensiones de terceros.

## Límite de pruebas ejecutadas antes de la entrega

El entorno de construcción dispone de PHP CLI para lint, pero no del runtime Composer, MySQL, Suwayomi Java local ni los paquetes Vue descargados. Por ello no se puede afirmar que `composer install`, `npm run build` o un recorrido real de las pantallas se hayan ejecutado satisfactoriamente. El usuario debe realizar el ciclo de pruebas del README al instalar. No se debe afirmar que todo funciona al 100 % sin esas pruebas.
