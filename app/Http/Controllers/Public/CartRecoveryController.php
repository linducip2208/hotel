<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Jobs\SendAbandonedCartReminderJob;
use App\Models\AbandonedCart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartRecoveryController extends Controller
{
    public function track(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'cart_data' => 'required|json',
            'session_id' => 'required|string|min:8',
            'guest_name' => 'nullable|string|max:255',
        ]);

        $cartData = json_decode($data['cart_data'], true);

        $recoveryToken = Str::random(64);

        $cart = AbandonedCart::updateOrCreate(
            ['session_id' => $data['session_id']],
            [
                'guest_email' => $data['email'],
                'guest_name' => $data['guest_name'] ?? '',
                'cart_data' => $cartData,
                'recovery_token' => $recoveryToken,
                'expires_at' => now()->addHours(24),
            ]
        );

        SendAbandonedCartReminderJob::dispatch(
            $data['session_id'],
            $data['email'],
            $data['guest_name'] ?? '',
            $cartData,
            $recoveryToken,
        )->delay(now()->addHour());

        return response()->json(['ok' => true, 'session_id' => $data['session_id']]);
    }

    public function recover(string $token)
    {
        $cart = AbandonedCart::where('recovery_token', $token)->firstOrFail();

        if ($cart->expires_at->isPast()) {
            abort(410, 'This recovery link has expired.');
        }

        if (! $cart->recovered_at) {
            $cart->update(['recovered_at' => now()]);
        }

        session()->put('booking_cart', $cart->cart_data);

        return redirect()->route('booking.checkout', $cart->cart_data);
    }
}
