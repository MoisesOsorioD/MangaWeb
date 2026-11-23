<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ExtensionsController;
use App\Http\Controllers\ReadingProgressController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function(){
 Route::get('/login',[AuthController::class,'loginForm'])->name('login');
 Route::post('/login',[AuthController::class,'login'])->middleware('throttle:8,1');
 Route::get('/register',[AuthController::class,'registerForm'])->name('register');
 Route::post('/register',[AuthController::class,'register'])->middleware('throttle:4,1');
});
Route::middleware('auth')->group(function(){
 Route::post('/logout',[AuthController::class,'logout'])->name('logout');
 Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
 Route::get('/explore',[ExploreController::class,'index'])->name('explore');
 Route::get('/explore/source/{sourceId}',[ExploreController::class,'catalog'])->whereNumber('sourceId')->name('catalog');
 Route::get('/manga/{mangaId}',[MangaController::class,'show'])->whereNumber('mangaId')->name('manga.show');
 Route::get('/manga/{mangaId}/thumbnail',[ImageController::class,'thumbnail'])->whereNumber('mangaId')->name('manga.thumbnail');
 Route::get('/manga/{mangaId}/chapter/{chapterIndex}/page/{pageIndex}',[ImageController::class,'page'])
  ->whereNumber('mangaId')->whereNumber('chapterIndex')->whereNumber('pageIndex')->name('manga.chapter.page');
 Route::get('/manga/{mangaId}/read/{chapterId}',[ReaderController::class,'show'])
  ->whereNumber('mangaId')->whereNumber('chapterId')->name('manga.read');
 Route::get('/library',[LibraryController::class,'index'])->name('library');
 Route::post('/library/toggle',[LibraryController::class,'toggle'])->name('library.toggle');
 Route::get('/history',[HistoryController::class,'index'])->name('history');
 Route::get('/extensions',[ExtensionsController::class,'index'])->name('extensions');
 Route::get('/settings',fn()=>\Inertia\Inertia::render('Settings',['registrationOpen'=>(bool)config('mangaweb.allow_registration')]))->name('settings');
 Route::get('/reading-progress/{mangaId}/{chapterId}',[ReadingProgressController::class,'show'])
  ->whereNumber('mangaId')->whereNumber('chapterId')->name('reading-progress.show');
 Route::post('/reading-progress',[ReadingProgressController::class,'store'])->name('reading-progress.store');
 // Biblioteca: colecciones, estados y puntuaciones.
 Route::patch('/library/{mangaId}',[LibraryController::class,'update'])->whereNumber('mangaId')->name('library.update');
 Route::get('/shelves',[\App\Http\Controllers\ShelvesController::class,'index'])->name('shelves.index');
 Route::post('/shelves',[\App\Http\Controllers\ShelvesController::class,'store'])->name('shelves.store');
 Route::delete('/shelves/{shelfId}',[\App\Http\Controllers\ShelvesController::class,'destroy'])->whereNumber('shelfId')->name('shelves.destroy');
 // Descargas privadas de capítulos mediante la cola Laravel.
 Route::get('/downloads',[\App\Http\Controllers\DownloadsController::class,'index'])->name('downloads.index');
 Route::post('/downloads',[\App\Http\Controllers\DownloadsController::class,'store'])->name('downloads.store');
 Route::delete('/downloads/{mangaId}/{chapterId}',[\App\Http\Controllers\DownloadsController::class,'destroy'])->whereNumber('mangaId')->whereNumber('chapterId')->name('downloads.destroy');
 Route::get('/downloads/{mangaId}/{chapterId}/cbz',[\App\Http\Controllers\DownloadsController::class,'archive'])->whereNumber('mangaId')->whereNumber('chapterId')->name('downloads.archive');
 Route::get('/cached-page/{mangaId}/{chapterId}/{pageIndex}',[\App\Http\Controllers\CachedPageController::class,'show'])->whereNumber('mangaId')->whereNumber('chapterId')->whereNumber('pageIndex')->name('cached.page');
 // Marcadores de páginas.
 Route::get('/bookmarks',[\App\Http\Controllers\BookmarksController::class,'index'])->name('bookmarks.index');
 Route::get('/bookmarks/{mangaId}/{chapterId}',[\App\Http\Controllers\BookmarksController::class,'forChapter'])->whereNumber('mangaId')->whereNumber('chapterId')->name('bookmarks.chapter');
 Route::post('/bookmarks/toggle',[\App\Http\Controllers\BookmarksController::class,'toggle'])->name('bookmarks.toggle');
 // Administración de extensiones, por defecto desactivada y restringida al dueño.
 Route::post('/extensions/manage',[ExtensionsController::class,'update'])->name('extensions.update');

 Route::get('/search',[\App\Http\Controllers\SearchController::class,'index'])->name('search');
 Route::patch('/profile',[\App\Http\Controllers\ProfileController::class,'update'])->name('profile.update');
 Route::put('/profile/password',[\App\Http\Controllers\ProfileController::class,'password'])->name('profile.password');
 Route::get('/backup/export',[\App\Http\Controllers\BackupController::class,'export'])->name('backup.export');
 Route::post('/backup/import',[\App\Http\Controllers\BackupController::class,'import'])->name('backup.import');
 Route::delete('/history/{progressId}',[HistoryController::class,'destroy'])->whereNumber('progressId')->name('history.destroy');
 Route::delete('/history',[HistoryController::class,'clear'])->name('history.clear');

});
