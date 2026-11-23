<?php
/** Verifica que el proyecto traiga sus archivos fundamentales. No sustituye una ejecución real. */
$base=dirname(__DIR__);
$needed=['artisan','bootstrap/app.php','app/Http/Controllers/Controller.php','app/Services/SuwayomiService.php','app/Jobs/CacheChapterJob.php','routes/web.php','resources/js/app.ts','resources/js/pages/Reader.vue','resources/js/pages/Downloads.vue','resources/js/pages/Library.vue','database/migrations/2026_01_01_000004_create_jobs_table.php','public/index.php','composer.json','package.json'];
$bad=[];foreach($needed as $file)if(!is_file($base.'/'.$file))$bad[]=$file;
if($bad){fwrite(STDERR,'Archivos faltantes: '.implode(', ',$bad).PHP_EOL);exit(1);}
$count=0;foreach(['app','bootstrap','config','database','public','resources','routes','tests'] as $dir){
 $phpFiles=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base.'/'.$dir,FilesystemIterator::SKIP_DOTS));
 foreach($phpFiles as $f){if($f->getExtension()!=='php')continue;$count++;$cmd=escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($f->getPathname());exec($cmd,$out,$exit);if($exit!==0){echo implode(PHP_EOL,$out);exit(1);} $out=[];}
}
echo "OK: {$count} archivos PHP sin errores de sintaxis y archivos esenciales presentes.
";
