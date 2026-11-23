<?php
namespace App\Jobs;

use App\Services\SuwayomiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CacheChapterJob implements ShouldQueue
{
 use Queueable;
 public int $timeout=900;
 public int $tries=2;
 public function __construct(public int $userId,public string $mangaId,public string $chapterId) {}
 public function handle(SuwayomiService $suwayomi):void
 {
  $keys=['user_id'=>$this->userId,'manga_id'=>$this->mangaId,'chapter_id'=>$this->chapterId];
  $disk=Storage::disk('local');
  $base='mangaweb/'.$this->userId.'/'.$this->mangaId.'/'.$this->chapterId;
  DB::table('chapter_downloads')->where($keys)->update(['status'=>'running','error'=>null,'updated_at'=>now()]);
  try {
   $paths=$suwayomi->chapterPages($this->chapterId);
   if(!is_array($paths)||count($paths)===0)throw new RuntimeException('El capítulo no tiene páginas.');
   if(count($paths)>config('mangaweb.download_max_pages'))throw new RuntimeException('Capítulo demasiado grande para descargar.');
   $files=[];
   foreach($paths as $index=>$path){
    if(!is_string($path)||!preg_match('~^/api/v1/manga/(\d+)/chapter/(\d+)/page/(\d+)$~D',$path,$m)||$m[1]!==$this->mangaId)throw new RuntimeException('Suwayomi devolvió una ruta de imagen incorrecta.');
    $image=$suwayomi->image($path);
    $type=$image['type'];
    $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','image/avif'=>'avif'][$type]??null;
    if(!$ext)throw new RuntimeException('Formato de imagen no soportado.');
    $name=str_pad((string)$index,5,'0',STR_PAD_LEFT).'.'.$ext;
    $rel=$base.'/'.$name;
    if(!$disk->put($rel,$image['body']))throw new RuntimeException('No se pudo guardar una página en el servidor.');
    $files[]=['file'=>$name,'type'=>$type??'image/jpeg'];
    DB::table('chapter_downloads')->where($keys)->update(['saved_pages'=>$index+1,'total_pages'=>count($paths),'updated_at'=>now()]);
   }
   $disk->put($base.'/manifest.json',json_encode(['pages'=>$files,'savedAt'=>now()->toIso8601String()],JSON_UNESCAPED_UNICODE));
   DB::table('chapter_downloads')->where($keys)->update(['status'=>'ready','total_pages'=>count($files),'saved_pages'=>count($files),'updated_at'=>now()]);
  }catch(Throwable $e){
   DB::table('chapter_downloads')->where($keys)->update(['status'=>'error','error'=>substr($e->getMessage(),0,900),'updated_at'=>now()]);
   throw $e;
  }
 }
}
