<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin() { return view('admin.login'); }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }
        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        return redirect()->route('admin.login');
    }
}
