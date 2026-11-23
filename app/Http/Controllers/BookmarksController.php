<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
class BookmarksController extends Controller {
 public function index(Request $r):Response {return Inertia::render('Bookmarks',['items'=>DB::table('reading_bookmarks')->where('user_id',$r->user()->id)->latest()->get()]);}
 public function toggle(Request $r):JsonResponse {
  $d=$r->validate(['manga_id'=>['required','string','max:64','regex:/^\d+$/'],'chapter_id'=>['required','string','max:64','regex:/^\d+$/'],'page_number'=>'required|integer|min:1|max:10000','label'=>'nullable|string|max:150']);
  $keys=['user_id'=>$r->user()->id,'manga_id'=>$d['manga_id'],'chapter_id'=>$d['chapter_id'],'page_number'=>$d['page_number']];
  if(DB::table('reading_bookmarks')->where($keys)->exists()){DB::table('reading_bookmarks')->where($keys)->delete();return response()->json(['bookmarked'=>false]);}
  DB::table('reading_bookmarks')->insert([...$keys,'label'=>$d['label']??null,'created_at'=>now(),'updated_at'=>now()]);return response()->json(['bookmarked'=>true]);
 }
 public function forChapter(Request $r,string $mangaId,string $chapterId):JsonResponse {
  $pages=DB::table('reading_bookmarks')->where('user_id',$r->user()->id)->where('manga_id',$mangaId)->where('chapter_id',$chapterId)->orderBy('page_number')->pluck('page_number');
  return response()->json(['pages'=>$pages]);
 }
}
