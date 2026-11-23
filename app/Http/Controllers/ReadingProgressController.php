<?php
namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class ReadingProgressController extends Controller {
 public function show(Request $r,string $mangaId,string $chapterId):JsonResponse {
  $row=DB::table('reading_progress')->where('user_id',$r->user()->id)->where('manga_id',$mangaId)->where('chapter_id',$chapterId)->first();
  return response()->json(['has_progress'=>(bool)$row,'page_number'=>(int)($row->page_number??1),'total_pages'=>(int)($row->total_pages??0)]);
 }
 public function store(Request $r):JsonResponse {
  $d=$r->validate([
   'manga_id'=>['required','string','max:64','regex:/^[0-9]+$/'],
   'chapter_id'=>['required','string','max:64','regex:/^[0-9]+$/'],
   'page_number'=>'required|integer|min:1|max:10000','total_pages'=>'required|integer|min:1|max:10000',
   'manga_title'=>'nullable|string|max:500','chapter_name'=>'nullable|string|max:500',
  ]);
  if ($d['page_number']>$d['total_pages']) throw ValidationException::withMessages(['page_number'=>'La página supera el total.']);
  $keys=['user_id'=>$r->user()->id,'manga_id'=>$d['manga_id'],'chapter_id'=>$d['chapter_id']];
  DB::table('reading_progress')->upsert([[
   ...$keys,'page_number'=>$d['page_number'],'total_pages'=>$d['total_pages'],
   'manga_title'=>$d['manga_title']??null,'chapter_name'=>$d['chapter_name']??null,
   'created_at'=>now(),'updated_at'=>now(),
  ]],['user_id','manga_id','chapter_id'],['page_number','total_pages','manga_title','chapter_name','updated_at']);
  return response()->json(['success'=>true,'page_number'=>$d['page_number']]);
 }
}
