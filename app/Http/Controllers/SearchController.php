<?php
namespace App\Http\Controllers;
use App\Services\SuwayomiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;
class SearchController extends Controller {
 public function index(Request $r,SuwayomiService $sw):Response {
  $d=$r->validate(['q'=>'nullable|string|max:120','source_id'=>['nullable','string','max:64','regex:/^\d+$/']]);
  $q=trim((string)($d['q']??''));$sources=[];$items=[];$error=null;
  try {
   $sources=$sw->sources();
   if($q!==''){
    // Buscar en una sola fuente o en hasta cinco para evitar saturar Suwayomi.
    $targets=empty($d['source_id'])?array_slice($sources,0,5):array_values(array_filter($sources,fn($s)=>(string)($s['id']??'')===$d['source_id']));
    foreach($targets as $source){
     try{$data=$sw->browse((string)$source['id'],1,'SEARCH',$q);
      foreach($data['mangas']??[] as $manga){$manga['sourceName']=$source['name']??'Fuente';$items[]=$manga;}}
     catch(Throwable $e){Log::warning('Search source error: '.$e->getMessage());}
    }
   }
  }catch(Throwable $e){$error='No se pudo conectar con Suwayomi.';}
  return Inertia::render('Search',['q'=>$q,'sourceId'=>$d['source_id']??'','sources'=>$sources,'items'=>$items,'error'=>$error]);
 }
}
