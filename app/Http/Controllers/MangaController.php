<?php
namespace App\Http\Controllers;
use App\Services\SuwayomiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;
class MangaController extends Controller {
 public function show(SuwayomiService $suwayomi,Request $r,string $mangaId):Response {
  $manga=[];$chapters=[];$error=null;
  try {$manga=$suwayomi->manga($mangaId); $chapters=$suwayomi->chapters($mangaId);}
  catch(Throwable $e){Log::warning('Suwayomi manga error: '.$e->getMessage());$error='No se pudieron cargar los detalles. Comprueba Suwayomi y vuelve a intentarlo.';}
  $favorite=DB::table('library_items')->where('user_id',$r->user()->id)->where('manga_id',$mangaId)->exists();
  $progress=DB::table('reading_progress')->where('user_id',$r->user()->id)->where('manga_id',$mangaId)->get()->keyBy('chapter_id');
  return Inertia::render('Manga',[
   'manga'=>$manga,'chapters'=>$chapters,'favorite'=>$favorite,'progress'=>$progress,'error'=>$error,'downloads'=>DB::table('chapter_downloads')->where('user_id',$r->user()->id)->where('manga_id',$mangaId)->get()->keyBy('chapter_id'),
  ]);
 }
}
