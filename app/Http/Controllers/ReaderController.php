<?php
namespace App\Http\Controllers;
use App\Services\SuwayomiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;
use Illuminate\Support\Facades\Storage;
class ReaderController extends Controller {
 public function show(SuwayomiService $suwayomi,Request $r,string $mangaId,string $chapterId):Response {
  $pages=[];$error=null;$chapters=[];$chapterName=null;$mangaTitle=null;
  $cached=DB::table('chapter_downloads')->where(['user_id'=>$r->user()->id,'manga_id'=>$mangaId,'chapter_id'=>$chapterId,'status'=>'ready'])->first();
  $base='mangaweb/'.$r->user()->id.'/'.$mangaId.'/'.$chapterId;
  if($cached && Storage::disk('local')->exists($base.'/manifest.json')){
   $manifest=json_decode((string)Storage::disk('local')->get($base.'/manifest.json'),true);
   foreach(($manifest['pages']??[]) as $index=>$item) $pages[]='/cached-page/'.$mangaId.'/'.$chapterId.'/'.$index;
   $chapterName=$cached->chapter_name;$mangaTitle=$cached->manga_title;
  }
  
  try {
   // Las rutas devueltas por GraphQL usan el índice de Suwayomi, NO el id del capítulo.
   foreach($pages ? [] : $suwayomi->chapterPages($chapterId) as $path){
    if(!is_string($path)) continue;
    if(!preg_match('~^/api/v1/manga/(\d+)/chapter/(\d+)/page/(\d+)$~D',$path,$m))continue;
    if($m[1]!==$mangaId)continue;
    $pages[]='/manga/'.$m[1].'/chapter/'.$m[2].'/page/'.$m[3];
   }
   $chapters=$suwayomi->chapters($mangaId);
   try {$mangaTitle=$suwayomi->manga($mangaId)['title']??null;}catch(Throwable $ignored){}
  }catch(Throwable $e){Log::warning('Suwayomi reader error: '.$e->getMessage());if(!$pages)$error='No pudimos cargar las páginas. Comprueba Suwayomi o descargá el capítulo previamente.';}
  $ids=array_map(fn($ch)=>(string)($ch['id']??''),$chapters);
  $pos=array_search($chapterId,$ids,true);
  if ($pos!==false) $chapterName=$chapters[$pos]['name']??$chapterName;
  $prev=$pos!==false && $pos>0 ? $ids[$pos-1]:null;
  $next=$pos!==false && $pos<count($ids)-1 ? $ids[$pos+1]:null;
  $saved=DB::table('reading_progress')->where('user_id',$r->user()->id)->where('manga_id',$mangaId)->where('chapter_id',$chapterId)->first();
  $favorite=DB::table('library_items')->where('user_id',$r->user()->id)->where('manga_id',$mangaId)->first();
  return Inertia::render('Reader',[
   'mangaId'=>$mangaId,'chapterId'=>$chapterId,'chapterName'=>$chapterName,
   'mangaTitle'=>$mangaTitle??($favorite->title??null),'pages'=>$pages,
   'savedPage'=>max(1,min((int)($saved->page_number??1),max(1,count($pages)))),
   'previousChapter'=>$prev,'nextChapter'=>$next,'error'=>$error,'downloaded'=>(bool)$cached,'startPage'=>(int)max(0,min(10000,(int)$r->query('page',0))),
  ]);
 }
}
