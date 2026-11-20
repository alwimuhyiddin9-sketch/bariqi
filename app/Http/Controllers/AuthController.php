<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $credentials['username'])->first();
        if (!$user || !password_verify($credentials['password'], $user->password)) {
            return back()->with('error', 'Nama pengguna atau kata sandi salah')->withInput();
        }

        session(['admin_user_id' => $user->id, 'admin_name' => $user->name]);
        return redirect()->route('dashboard')->with('success', 'Selamat datang, ' . $user->name);
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }
}
