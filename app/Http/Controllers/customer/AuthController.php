<?php

namespace App\Http\Controllers\customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\Controller;

use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.signup');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required',
            'password' => [
                'required',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'confirmed',
            ],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
        ]);

        Auth::login($user);
        return redirect()->route('verification.notice')->with('success', 'Akun berhasil dibuat. Silakan verifikasi email Anda!');
    }

    public function showLoginForm()
    {
        return view('auth.signin');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => [
                'required',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            return redirect('/')->with('success', 'Berhasil login!');
        }
        return back()->withErrors(['email' => 'Email atau password salah'])->withInput();
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/')->with('success', 'Berhasil logout!');
    }
} 