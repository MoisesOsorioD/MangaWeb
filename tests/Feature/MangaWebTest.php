<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
class MangaWebTest extends TestCase {
 use RefreshDatabase;
 public function test_guest_is_redirected_to_login():void { $this->get('/dashboard')->assertRedirect('/login'); }
 public function test_user_can_access_dashboard_and_library():void {
  $user=User::create(['name'=>'Test','email'=>'test@example.test','password'=>'Password123!']);
  $this->actingAs($user)->get('/dashboard')->assertOk();
  $this->actingAs($user)->get('/library')->assertOk();
 }
 public function test_user_can_save_reading_progress():void {
  $user=User::create(['name'=>'Test','email'=>'test2@example.test','password'=>'Password123!']);
  $this->actingAs($user)->postJson('/reading-progress',['manga_id'=>'1','chapter_id'=>'24','page_number'=>3,'total_pages'=>20])->assertOk();
  $this->actingAs($user)->getJson('/reading-progress/1/24')->assertJsonPath('page_number',3);
 }
 public function test_user_cannot_access_another_users_reading_progress():void {
  $a=User::create(['name'=>'A','email'=>'a@example.test','password'=>'Password123!']);
  $b=User::create(['name'=>'B','email'=>'b@example.test','password'=>'Password123!']);
  $this->actingAs($a)->postJson('/reading-progress',['manga_id'=>'1','chapter_id'=>'24','page_number'=>5,'total_pages'=>20])->assertOk();
  $this->actingAs($b)->getJson('/reading-progress/1/24')->assertJsonPath('has_progress',false);
 }
 public function test_catalog_uses_suwayomi_graphql():void {
  $user=User::create(['name'=>'Test','email'=>'test3@example.test','password'=>'Password123!']);
  Http::fake(['*/api/graphql'=>Http::response(['data'=>['fetchSourceManga'=>['mangas'=>[['id'=>1,'title'=>'Demo','thumbnailUrl'=>'']],'hasNextPage'=>false]]],200)]);
  $this->actingAs($user)->get('/explore/source/6400665728063187402')->assertOk();
 }
}
