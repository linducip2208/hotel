<?php

namespace App\Http\Controllers\Panel\Loyalty;

use App\Http\Controllers\Controller;
use App\Models\GiftVoucher;
use App\Models\Guest;
use App\Models\LoyaltyMember;
use App\Models\LoyaltyTier;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoyaltyController extends Controller
{
    public function members(Request $request)
    {
        $members = LoyaltyMember::where('property_id', app('current_property')->id)
            ->with('guest', 'tier')->paginate(50);
        return view('panel.loyalty.members', compact('members'));
    }

    public function enroll(Request $request, LoyaltyService $svc)
    {
        $guest = Guest::where('property_id', app('current_property')->id)->findOrFail($request->input('guest_id'));
        $svc->enroll($guest);
        return back();
    }

    public function tiers()
    {
        $tiers = LoyaltyTier::where('property_id', app('current_property')->id)->orderBy('points_threshold')->get();
        return view('panel.loyalty.tiers', compact('tiers'));
    }

    public function storeTier(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string',
            'points_threshold' => 'required|integer|min:0',
            'rate_discount_pct' => 'nullable|numeric',
        ]);
        LoyaltyTier::create($data + ['property_id' => app('current_property')->id]);
        return back();
    }

    public function vouchers(Request $request)
    {
        $vouchers = GiftVoucher::where('property_id', app('current_property')->id)
            ->orderByDesc('issued_at')->paginate(50);
        return view('panel.loyalty.vouchers', compact('vouchers'));
    }

    public function issueVoucher(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:amount,night,package,spa,fnb',
            'face_value' => 'required|numeric|min:0',
            'valid_until' => 'nullable|date',
            'issued_to_email' => 'nullable|email',
            'issued_to_phone' => 'nullable|string',
            'message' => 'nullable|string',
        ]);
        GiftVoucher::create($data + [
            'property_id' => app('current_property')->id,
            'code' => 'GV-'.strtoupper(Str::random(10)),
            'balance' => $data['face_value'],
            'currency' => 'IDR',
            'issued_by_user_id' => $request->user()?->id,
            'status' => 'active',
            'issued_at' => now(),
        ]);
        return back();
    }
}
