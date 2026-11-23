<?php
namespace App\Http\Controllers;
use App\Services\SuwayomiService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;
class ExtensionsController extends Controller {
 public function index(SuwayomiService $suwayomi,\Illuminate\Http\Request $request):Response {
  $extensions=[];$error=null;
  try{$extensions=$suwayomi->extensions();}
  catch(Throwable $e){Log::warning('Suwayomi extensions error: '.$e->getMessage());$error='No se pudo leer la lista de extensiones de Suwayomi.';}
  return Inertia::render('Extensions',['extensions'=>$extensions,'error'=>$error,'canManage'=>config('mangaweb.allow_extension_management') && (int)$request->user()->id===1]);
 }
 public function update(\Illuminate\Http\Request $r,SuwayomiService $suwayomi):\Illuminate\Http\RedirectResponse {
  abort_unless(config('mangaweb.allow_extension_management'),403,'Activá ALLOW_EXTENSION_MANAGEMENT=true en .env.');
  abort_unless((int)$r->user()->id===1,403,'Solo la primera cuenta puede administrar extensiones.');
  $data=$r->validate(['pkg_name'=>'required|string|max:250|regex:/^[a-zA-Z0-9._-]+$/','action'=>'required|in:install,update,uninstall']);
  // Aceptamos únicamente paquetes ya listados en el repositorio de Suwayomi.
  $list=$suwayomi->extensions();
  abort_unless(collect($list)->contains(fn($e)=>($e['pkgName']??'')===$data['pkg_name']),404,'La extensión no aparece en Suwayomi.');
  $suwayomi->updateExtension($data['pkg_name'],$data['action']);
  return back()->with('success','Operación solicitada a Suwayomi.');
 }

}
