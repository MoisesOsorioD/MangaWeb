<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
class HistoryController extends Controller {
 public function index(Request $r):Response {
  return Inertia::render('History',['items'=>DB::table('reading_progress')->where('user_id',$r->user()->id)->orderByDesc('updated_at')->limit(500)->get()]);
 }
 public function destroy(Request $r,string $progressId):RedirectResponse {
  DB::table('reading_progress')->where('user_id',$r->user()->id)->where('id',$progressId)->delete();return back()->with('success','Lectura eliminada del historial.');
 }
 public function clear(Request $r):RedirectResponse {
  DB::table('reading_progress')->where('user_id',$r->user()->id)->delete();return back()->with('success','Historial eliminado.');
 }
}
