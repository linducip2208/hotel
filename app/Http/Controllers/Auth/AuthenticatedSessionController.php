<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::validate($request->only('email', 'password'))) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        $user = \App\Models\User::where('email', $request->input('email'))->first();

        if ($user->two_factor_enabled) {
            session([
                'two_factor_pending' => true,
                'two_factor_user_id' => $user->id,
                'two_factor_remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return redirect()->intended('/panel');
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
