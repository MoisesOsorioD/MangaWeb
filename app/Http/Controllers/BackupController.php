<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
class BackupController extends Controller {
 private const TABLES=['shelves','library_items','reading_progress','reading_bookmarks'];
 public function export(Request $r):\Illuminate\Http\JsonResponse {
  $data=['format'=>'mangaweb-backup-v1','exported_at'=>now()->toIso8601String()];
  foreach(self::TABLES as $name){$rows=DB::table($name)->where('user_id',$r->user()->id)->get()->map(function($row)use($name,$r){$a=(array)$row;if($name==='library_items'&&$row->shelf_id){$a['shelf_name']=DB::table('shelves')->where('user_id',$r->user()->id)->where('id',$row->shelf_id)->value('name');}unset($a['id'],$a['user_id']);return $a;})->toArray();$data[$name]=$rows;}
  return response()->json($data,200,['Content-Disposition'=>'attachment; filename="mangaweb-respaldo.json"','Cache-Control'=>'no-store']);
 }
 public function import(Request $r):RedirectResponse {
  $r->validate(['backup'=>'required|file|max:2048']);
  $data=json_decode((string)file_get_contents($r->file('backup')->getRealPath()),true);
  if(!is_array($data)||($data['format']??'')!=='mangaweb-backup-v1')return back()->withErrors(['backup'=>'Formato de respaldo no reconocido.']);
  foreach(self::TABLES as $name){if(!isset($data[$name])||!is_array($data[$name])||count($data[$name])>10000)return back()->withErrors(['backup'=>'Datos incompletos o excesivos en '.$name]);}
  $uid=(int)$r->user()->id;
  DB::transaction(function()use($data,$uid){
   $shelfMap=[];
   foreach($data['shelves'] as $row){$name=substr((string)($row['name']??''),0,80);if($name==='')continue;
    DB::table('shelves')->updateOrInsert(['user_id'=>$uid,'name'=>$name],['updated_at'=>now(),'created_at'=>now()]);$shelfMap[(string)($row['id']??'')]=$name;}
   foreach($data['library_items'] as $row){$mid=(string)($row['manga_id']??'');if(!preg_match('/^\d{1,64}$/',$mid))continue;
    $shelf=null;$name=(string)($row['shelf_name']??'');if($name!=='')$shelf=DB::table('shelves')->where('user_id',$uid)->where('name',$name)->value('id');
    DB::table('library_items')->updateOrInsert(['user_id'=>$uid,'manga_id'=>$mid],[
      'title'=>substr((string)($row['title']??'Manga'),0,500),'source_id'=>!empty($row['source_id'])?substr((string)$row['source_id'],0,64):null,
      'reading_status'=>in_array($row['reading_status']??'', ['planned','reading','completed','on_hold','dropped'],true)?$row['reading_status']:'planned',
      'notes'=>substr((string)($row['notes']??''),0,3000),'rating'=>is_numeric($row['rating']??null)?max(1,min(5,(int)$row['rating'])):null,
      'shelf_id'=>$shelf,'created_at'=>now(),'updated_at'=>now(),]);}
   foreach($data['reading_progress'] as $row){$mid=(string)($row['manga_id']??'');$ch=(string)($row['chapter_id']??'');if(!preg_match('/^\d{1,64}$/',$mid)||!preg_match('/^\d{1,64}$/',$ch))continue;
    $total=max(1,min(10000,(int)($row['total_pages']??1)));$page=max(1,min($total,(int)($row['page_number']??1)));
    DB::table('reading_progress')->updateOrInsert(['user_id'=>$uid,'manga_id'=>$mid,'chapter_id'=>$ch],[
      'manga_title'=>substr((string)($row['manga_title']??''),0,500),'chapter_name'=>substr((string)($row['chapter_name']??''),0,500),
      'page_number'=>$page,'total_pages'=>$total,'created_at'=>now(),'updated_at'=>now(),]);}
   foreach($data['reading_bookmarks'] as $row){$mid=(string)($row['manga_id']??'');$ch=(string)($row['chapter_id']??'');$page=(int)($row['page_number']??0);
    if(!preg_match('/^\d{1,64}$/',$mid)||!preg_match('/^\d{1,64}$/',$ch)||$page<1||$page>10000)continue;
    DB::table('reading_bookmarks')->updateOrInsert(['user_id'=>$uid,'manga_id'=>$mid,'chapter_id'=>$ch,'page_number'=>$page],[
      'label'=>substr((string)($row['label']??''),0,150),'created_at'=>now(),'updated_at'=>now(),]);}
  });
  return back()->with('success','Respaldo importado a tu cuenta.');
 }
}
