<?php
namespace App\Http\Controllers;
use App\Services\SuwayomiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ExploreController extends Controller {
 public function index(SuwayomiService $suwayomi, Request $r):Response {
  $sources=[];$error=null;
  try {$sources=$suwayomi->sources();}
  catch(Throwable $e){Log::warning('Suwayomi source error: '.$e->getMessage());$error='No se pudo conectar con Suwayomi. Revisa SUWAYOMI_URL y que el servidor Java esté encendido.';}
  return Inertia::render('Explore',['sources'=>$sources,'error'=>$error]);
 }
 public function catalog(SuwayomiService $suwayomi, Request $r, string $sourceId):Response {
  $r->validate(['page'=>'sometimes|integer|min:1|max:10000','type'=>'sometimes|in:POPULAR,LATEST,SEARCH','q'=>'nullable|string|max:150']);
  $page=(int) $r->query('page',1);$type=(string) $r->query('type','POPULAR');$search=trim((string)$r->query('q',''));
  if ($type==='SEARCH' && $search==='') $type='POPULAR';
  $result=['hasNextPage'=>false,'mangas'=>[]];$error=null;
  try{$result=$suwayomi->browse($sourceId,$page,$type,$search);}
  catch(Throwable $e){Log::warning('Suwayomi browse error: '.$e->getMessage());$error='No se pudo cargar este catálogo. Revisa que la fuente siga instalada y funcionando.';}
  return Inertia::render('Catalog',[
   'sourceId'=>$sourceId,'mangas'=>$result['mangas']??[], 'hasNextPage'=>(bool)($result['hasNextPage']??false),
   'page'=>$page,'type'=>$type,'q'=>$search,'error'=>$error,
  ]);
 }
}
