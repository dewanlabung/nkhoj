<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Auth::check() ? redirect('/dashboard') : view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            if ($user->two_factor_enabled) {
                // Store user ID for 2FA challenge and log out temporarily
                session(['2fa_user_id' => $user->id, '2fa_remember' => $request->boolean('remember')]);
                Auth::logout();
                return redirect('/two-factor-challenge');
            }
            try { LoginHistory::record($user->id, 'email'); } catch (\Throwable) {}
            return redirect()->intended('/dashboard');
        }

        // Record failed attempt if user exists
        try {
            $failedUser = User::where('email', $credentials['email'])->first();
            if ($failedUser) {
                LoginHistory::record($failedUser->id, 'email', false);
            }
        } catch (\Throwable) {}

        return back()->withErrors(['email' => 'These credentials do not match our records.']);
    }

    public function showRegister()
    {
        return Auth::check() ? redirect('/dashboard') : view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users|alpha_dash',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'uuid'     => Str::uuid(),
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'author',
        ]);

        Auth::login($user);
        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
