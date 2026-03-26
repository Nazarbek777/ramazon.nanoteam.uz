<?php

namespace App\Modules\Bookstore\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Bookstore/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('bookstore')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('bookstore.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Berilgan ma’lumotlar bizning qaydlarimizga mos kelmadi.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('bookstore')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('bookstore.login');
    }
}
