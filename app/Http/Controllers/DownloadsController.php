<?php
namespace App\Http\Controllers;

use App\Jobs\CacheChapterJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class DownloadsController extends Controller
{
 public function index(Request $request):Response {
  return Inertia::render('Downloads',['items'=>DB::table('chapter_downloads')->where('user_id',$request->user()->id)->latest()->get()]);
 }
 public function store(Request $r):RedirectResponse {
  $data=$r->validate(['manga_id'=>['required','string','max:64','regex:/^\d+$/'],'chapter_id'=>['required','string','max:64','regex:/^\d+$/'],'manga_title'=>'nullable|string|max:500','chapter_name'=>'nullable|string|max:500']);
  $keys=['user_id'=>$r->user()->id,'manga_id'=>$data['manga_id'],'chapter_id'=>$data['chapter_id']];
  $row=DB::table('chapter_downloads')->where($keys)->first();
  if($row && in_array($row->status,['queued','running','ready'],true)) return back()->with('success','La descarga ya está registrada.');
  DB::table('chapter_downloads')->updateOrInsert($keys,[...array_intersect_key($data,array_flip(['manga_title','chapter_name'])),'status'=>'queued','saved_pages'=>0,'total_pages'=>0,'error'=>null,'updated_at'=>now(),'created_at'=>$row->created_at??now()]);
  CacheChapterJob::dispatch((int)$keys['user_id'],$keys['manga_id'],$keys['chapter_id']);
  return back()->with('success','Descarga añadida a la cola. Ejecutá INICIAR_DESCARGAS.bat.');
 }
 public function destroy(Request $r,string $mangaId,string $chapterId):RedirectResponse {
  $keys=['user_id'=>$r->user()->id,'manga_id'=>$mangaId,'chapter_id'=>$chapterId];
  $download=DB::table('chapter_downloads')->where($keys)->first();
  abort_unless($download,404);
  abort_if(in_array($download->status,['queued','running'],true),409,'Esperá a que la descarga termine.');
  Storage::disk('local')->deleteDirectory('mangaweb/'.$r->user()->id.'/'.$mangaId.'/'.$chapterId);
  DB::table('chapter_downloads')->where($keys)->delete();
  return back()->with('success','Descarga eliminada.');
 }
 public function archive(Request $r,string $mangaId,string $chapterId):BinaryFileResponse {
  abort_unless(class_exists(ZipArchive::class),500,'Activá ext-zip en PHP para exportar CBZ.');
  $download=DB::table('chapter_downloads')->where(['user_id'=>$r->user()->id,'manga_id'=>$mangaId,'chapter_id'=>$chapterId,'status'=>'ready'])->first();
  abort_unless($download,404);
  $disk=Storage::disk('local');$base='mangaweb/'.$r->user()->id.'/'.$mangaId.'/'.$chapterId;
  $manifest=json_decode((string)$disk->get($base.'/manifest.json'),true);
  abort_unless(is_array($manifest)&&!empty($manifest['pages']),404);
  $tmp=tempnam(sys_get_temp_dir(),'mangaweb_cbz_');$zip=new ZipArchive();
  if($zip->open($tmp,ZipArchive::OVERWRITE)!==true){@unlink($tmp);abort(500,'No se pudo crear CBZ.');}
  foreach($manifest['pages'] as $item){$name=basename((string)($item['file']??''));if(!preg_match('/^\d{5}\.(jpg|png|webp|gif|avif)$/',$name))continue;
   $path=$base.'/'.$name;if($disk->exists($path))$zip->addFromString($name,$disk->get($path));}
  $zip->close();
  return response()->download($tmp,'manga-'.$mangaId.'-capitulo-'.$chapterId.'.cbz',['Content-Type'=>'application/vnd.comicbook+zip'])->deleteFileAfterSend(true);
 }
}
