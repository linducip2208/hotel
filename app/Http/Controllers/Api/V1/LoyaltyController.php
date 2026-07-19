<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GiftVoucher;
use App\Models\Guest;
use App\Models\LoyaltyMember;
use App\Models\Property;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function members()
    {
        return response()->json(
            LoyaltyMember::where('property_id', $this->property()->id)
                ->with('guest', 'tier')
                ->paginate(50)
        );
    }

    public function show(int $id)
    {
        $member = LoyaltyMember::where('property_id', $this->property()->id)
            ->with('guest', 'tier', 'transactions')
            ->findOrFail($id);

        return response()->json($member);
    }

    public function enroll(Request $request, LoyaltyService $svc)
    {
        $request->validate([
            'guest_id' => 'required|integer|exists:guests,id',
        ]);

        $guest = Guest::where('property_id', $this->property()->id)
            ->findOrFail($request->input('guest_id'));

        return response()->json($svc->enroll($guest), 201);
    }

    public function redeem(Request $request, int $id, LoyaltyService $svc)
    {
        $request->validate([
            'points'      => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $member = LoyaltyMember::where('property_id', $this->property()->id)->findOrFail($id);

        $tx = $svc->redeem(
            $member,
            (int) $request->input('points'),
            $request->input('description', 'Redeem')
        );

        return $tx
            ? response()->json($tx)
            : response()->json(['error' => 'Insufficient points'], 422);
    }

    public function vouchers(Request $request)
    {
        $query = GiftVoucher::where('property_id', $this->property()->id)
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest();

        return response()->json($query->paginate(50));
    }
}
