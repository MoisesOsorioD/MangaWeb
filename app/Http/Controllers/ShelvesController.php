<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
class ShelvesController extends Controller {
 public function index(Request $r):Response {
  $uid=$r->user()->id;
  return Inertia::render('Shelves',['shelves'=>DB::table('shelves')->where('user_id',$uid)->orderBy('name')->get(),'items'=>DB::table('library_items')->where('user_id',$uid)->orderBy('title')->get()]);
 }
 public function store(Request $r):RedirectResponse {
  $d=$r->validate(['name'=>'required|string|min:1|max:80']);
  $exists=DB::table('shelves')->where('user_id',$r->user()->id)->where('name',$d['name'])->exists();
  if(!$exists)DB::table('shelves')->insert(['user_id'=>$r->user()->id,'name'=>$d['name'],'created_at'=>now(),'updated_at'=>now()]);
  return back()->with('success','Colección creada.');
 }
 public function destroy(Request $r,string $shelfId):RedirectResponse {
  DB::table('shelves')->where('user_id',$r->user()->id)->where('id',$shelfId)->delete();return back()->with('success','Colección eliminada; los mangas siguen en Biblioteca.');
 }
}
