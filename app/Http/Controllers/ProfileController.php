<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class ProfileController extends Controller {
 public function update(Request $r):RedirectResponse {
  $data=$r->validate(['name'=>'required|string|min:2|max:80','email'=>['required','email','max:255',Rule::unique('users','email')->ignore($r->user()->id)]]);
  $r->user()->update($data);
  return back()->with('success','Perfil actualizado.');
 }
 public function password(Request $r):RedirectResponse {
  $d=$r->validate(['current_password'=>'required|current_password','password'=>'required|string|min:10|confirmed']);
  $r->user()->update(['password'=>$d['password']]);
  return back()->with('success','Contraseña actualizada.');
 }
}
