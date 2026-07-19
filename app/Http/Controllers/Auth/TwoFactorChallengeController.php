<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function create()
    {
        if (! session('two_factor_pending')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::findOrFail(session('two_factor_user_id'));

        if (! $user->verifyTwoFactorCode($request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two-factor authentication code is invalid.')],
            ]);
        }

        Auth::login($user, session('two_factor_remember', false));

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        session()->forget(['two_factor_pending', 'two_factor_user_id', 'two_factor_remember']);

        $request->session()->regenerate();

        return redirect()->intended('/panel');
    }

    public function verifyRecoveryCode(Request $request)
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::findOrFail(session('two_factor_user_id'));

        if (! $user->consumeRecoveryCode($request->input('recovery_code'))) {
            throw ValidationException::withMessages([
                'recovery_code' => [__('The provided recovery code is invalid or has already been used.')],
            ]);
        }

        Auth::login($user, session('two_factor_remember', false));

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        session()->forget(['two_factor_pending', 'two_factor_user_id', 'two_factor_remember']);

        $request->session()->regenerate();

        return redirect()->intended('/panel');
    }

    public function setup(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->two_factor_enabled) {
            return redirect()->route('two-factor.recovery');
        }

        $secret = $user->generateTwoFactorSecret();
        $qrCodeUrl = $user->getTwoFactorQrCodeUrl($secret);

        session(['two_factor_setup_secret' => $secret]);

        return view('auth.two-factor-setup', compact('qrCodeUrl', 'secret'));
    }

    public function enable(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $secret = session('two_factor_setup_secret');

        if (! $secret) {
            return redirect()->route('two-factor.setup')
                ->withErrors(['code' => __('Two-factor setup expired. Please try again.')]);
        }

        $google2fa = app(Google2FA::class);

        if (! $google2fa->verifyKey($secret, $request->input('code'), 2)) {
            throw ValidationException::withMessages([
                'code' => [__('The provided code is invalid. Please try again.')],
            ]);
        }

        $user->update([
            'two_factor_secret_encrypted' => encrypt($secret),
        ]);

        $user->enableTwoFactor();

        session()->forget('two_factor_setup_secret');

        return redirect()->route('two-factor.recovery')
            ->with('status', __('Two-factor authentication has been enabled.'));
    }

    public function disable(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user->disableTwoFactor();

        return redirect()->route('panel.dashboard')
            ->with('status', __('Two-factor authentication has been disabled.'));
    }

    public function recoveryCodes(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $codes = $user->two_factor_recovery_codes;

        if (empty($codes)) {
            $codes = $user->generateRecoveryCodes();
            $user->update(['two_factor_recovery_codes' => $codes]);
        }

        return view('auth.two-factor-recovery-codes', compact('codes'));
    }

    public function manage(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return view('panel.settings.two-factor', [
            'twoFactorEnabled' => $user->two_factor_enabled,
        ]);
    }
}
