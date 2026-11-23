<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function loginForm(): Response { return Inertia::render('Login'); }
    public function registerForm(): Response
    {
        abort_unless(config('mangaweb.allow_registration'), 404);
        return Inertia::render('Register');
    }
    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate(['email'=>'required|email','password'=>'required|string']);
        if (!Auth::attempt($data, $request->boolean('remember'))) {
            return back()->withErrors(['email'=>'Correo o contraseña incorrectos.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }
    public function register(Request $request): RedirectResponse
    {
        abort_unless(config('mangaweb.allow_registration'), 404);
        $data = $request->validate([
            'name'=>'required|string|max:80', 'email'=>'required|email|max:255|unique:users,email',
            'password'=>'required|string|min:10|confirmed',
        ]);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('dashboard');
    }
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
