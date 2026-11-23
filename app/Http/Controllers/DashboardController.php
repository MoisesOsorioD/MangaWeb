<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
class DashboardController extends Controller {
 public function index(Request $r):Response {
  $uid=$r->user()->id;
  return Inertia::render('Dashboard',[
   'libraryCount'=>DB::table('library_items')->where('user_id',$uid)->count(),
   'recentLibrary'=>DB::table('library_items')->where('user_id',$uid)->latest()->limit(8)->get(),
   'recentReading'=>DB::table('reading_progress')->where('user_id',$uid)->latest('updated_at')->limit(6)->get(),
  ]);
 }
}
