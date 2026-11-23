<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class CachedPageController extends Controller {
 public function show(Request $r,string $mangaId,string $chapterId,string $pageIndex):Response {
  $ready=DB::table('chapter_downloads')->where(['user_id'=>$r->user()->id,'manga_id'=>$mangaId,'chapter_id'=>$chapterId,'status'=>'ready'])->exists();
  abort_unless($ready,404);
  $base='mangaweb/'.$r->user()->id.'/'.$mangaId.'/'.$chapterId;
  $disk=Storage::disk('local');
  $manifest=json_decode((string)$disk->get($base.'/manifest.json'),true);
  $entry=$manifest['pages'][(int)$pageIndex]??null;
  abort_unless($entry,404);
  $name=basename((string)($entry['file']??''));
  abort_unless(preg_match('/^\d{5}\.(jpg|png|webp|gif|avif)$/',$name),404);
  $file=$base.'/'.$name;abort_unless($disk->exists($file),404);
  return response($disk->get($file),200,['Content-Type'=>(string)($entry['type']??'image/jpeg'),'Cache-Control'=>'private, max-age=3600','X-Content-Type-Options'=>'nosniff']);
 }
}
