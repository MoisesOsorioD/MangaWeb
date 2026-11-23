<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller {
 public function index(Request $r):Response {
  return Inertia::render('Library',['items'=>DB::table('library_items')->where('user_id',$r->user()->id)->orderByDesc('updated_at')->get(),'shelves'=>DB::table('shelves')->where('user_id',$r->user()->id)->orderBy('name')->get()]);
 }
 public function toggle(Request $r):RedirectResponse {
  $data=$r->validate([
   'manga_id'=>['required','string','max:64','regex:/^[0-9]+$/'],
   'source_id'=>['nullable','string','max:64','regex:/^[0-9]+$/'],
   'title'=>'required|string|max:500',
  ]);
  $keys=['user_id'=>$r->user()->id,'manga_id'=>$data['manga_id']];
  $existing=DB::table('library_items')->where($keys)->first();
  if ($existing) {DB::table('library_items')->where($keys)->delete();return back()->with('success','Manga eliminado de Biblioteca.');}
  DB::table('library_items')->insert([...$keys,'source_id'=>$data['source_id']??null,'title'=>$data['title'],'created_at'=>now(),'updated_at'=>now()]);
  return back()->with('success','Manga guardado en Biblioteca.');
 }
 public function update(Request $r,string $mangaId):RedirectResponse {
  $d=$r->validate(['reading_status'=>'required|in:planned,reading,completed,on_hold,dropped','notes'=>'nullable|string|max:3000','rating'=>'nullable|integer|min:1|max:5','shelf_id'=>'nullable|integer']);
  if(!empty($d['shelf_id']))abort_unless(DB::table('shelves')->where('user_id',$r->user()->id)->where('id',$d['shelf_id'])->exists(),422);
  $count=DB::table('library_items')->where('user_id',$r->user()->id)->where('manga_id',$mangaId)->update(['reading_status'=>$d['reading_status'],'notes'=>$d['notes']??null,'rating'=>$d['rating']??null,'shelf_id'=>$d['shelf_id']??null,'updated_at'=>now()]);
  abort_unless($count,404);return back()->with('success','Información de Biblioteca actualizada.');
 }

}
