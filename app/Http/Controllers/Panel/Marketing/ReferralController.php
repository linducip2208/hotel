<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Services\Marketing\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function index()
    {
        $propertyId = app('current_property')->id;
        $service = app(ReferralService::class);

        $stats = $service->getReferralStats($propertyId);
        $topReferrers = $service->getTopReferrers($propertyId, 10);
        $recentReferrals = Referral::where('property_id', $propertyId)
            ->with(['referrerGuest', 'referredGuest', 'referralCode'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();
        $codes = ReferralCode::where('property_id', $propertyId)
            ->with('ownerGuest')
            ->orderByDesc('created_at')
            ->paginate(30);
        $guests = Guest::where('property_id', $propertyId)
            ->whereNull('referral_code_id')
            ->orderBy('first_name')
            ->get();

        return view('panel.marketing.referrals', compact('stats', 'topReferrers', 'recentReferrals', 'codes', 'guests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_guest_id' => 'required|integer',
            'referrer_reward_amount' => 'nullable|numeric',
            'referee_discount_pct' => 'nullable|numeric|max:100',
            'uses_limit' => 'nullable|integer',
        ]);
        $guest = Guest::where('property_id', app('current_property')->id)->findOrFail($data['owner_guest_id']);
        ReferralCode::create($data + [
            'property_id' => app('current_property')->id,
            'code' => 'REF-'.strtoupper(substr(str_replace(' ', '', $guest->first_name), 0, 4)).'-'.strtoupper(Str::random(4)),
            'is_active' => true,
        ]);
        return back()->with('success', 'Referral code created.');
    }

    public function generateCode(Request $request, ReferralService $service)
    {
        $data = $request->validate([
            'guest_id' => 'required|integer',
        ]);
        $guest = Guest::where('property_id', app('current_property')->id)->findOrFail($data['guest_id']);
        $code = $service->generateCode($guest);
        return back()->with('success', 'Referral code generated: ' . $code->code);
    }

    public function settings()
    {
        return view('panel.marketing.referrals-settings');
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'default_reward_amount' => 'required|numeric|min:0',
            'default_discount_pct' => 'nullable|numeric|min:0|max:100',
            'reward_type' => 'required|string|in:discount,cashback,points,voucher',
        ]);

        session([
            'referral_default_reward' => $data['default_reward_amount'],
            'referral_default_discount' => $data['default_discount_pct'] ?? 0,
            'referral_reward_type' => $data['reward_type'],
        ]);

        return back()->with('success', 'Pengaturan referral disimpan.');
    }
}
