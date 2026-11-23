<?php
namespace App\Http\Controllers;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Throwable;
class ImageController extends Controller {
 public function thumbnail(string $mangaId):Response {return $this->proxy('/api/v1/manga/'.$mangaId.'/thumbnail');}
 public function page(string $mangaId,string $chapterIndex,string $pageIndex):Response {
  return $this->proxy('/api/v1/manga/'.$mangaId.'/chapter/'.$chapterIndex.'/page/'.$pageIndex);
 }
 private function proxy(string $path):Response {
  $url=rtrim((string)config('services.suwayomi.url'),'/').$path;
  $request=Http::timeout(35)->connectTimeout(5)->withOptions(['allow_redirects'=>false]);
  $username=(string)config('services.suwayomi.username');
  if($username!=='')$request=$request->withBasicAuth($username,(string)config('services.suwayomi.password'));
  try{$image=$request->get($url);}catch(Throwable $e){abort(502,'No se pudo conectar a Suwayomi.');}
  abort_unless($image->successful(),502,'Suwayomi no devolvió la imagen.');
  $type=strtolower(trim(explode(';',(string)$image->header('Content-Type'))[0]));
  if($type==='image/jpg')$type='image/jpeg';
  abort_unless(in_array($type,['image/jpeg','image/png','image/webp','image/gif','image/avif'],true),502,'Formato no válido.');
  return response($image->body(),200,['Content-Type'=>$type,'Cache-Control'=>'private, max-age=3600','X-Content-Type-Options'=>'nosniff']);
 }
}
